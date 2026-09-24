<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use App\Models\FeeSetting;
use App\Models\Passenger;
use App\Services\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FeePaymentController extends Controller
{
    /*
    |--------------------------------------------------------------------
    | ADMIN / INCHARGE
    |--------------------------------------------------------------------
    */

    // Fee settings + pending review queue + per-month fee records table
    public function index(Request $request)
    {
        $setting = FeeSetting::current();

        $month = $request->query('month') ?: now()->format('Y-m');
        // Guard against garbage query strings ending up in Carbon::createFromFormat below
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = now()->format('Y-m');
        }

        $pendingPayments = FeePayment::with('passenger')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $passengers = Passenger::where('approval_status', 'approved')
            ->with(['feePayments' => function ($q) use ($month) {
                $q->where('month', $month);
            }])
            ->orderBy('name')
            ->get();

        $todayStr = now()->toDateString();
        $isPastOrCurrentMonth = $month <= now()->format('Y-m');

        $records = $passengers->map(function (Passenger $p) use ($month, $todayStr, $isPastOrCurrentMonth) {
            $payment = $p->feePayments->first(); // already scoped to $month

            if ($payment) {
                $status = match (true) {
                    $payment->status === 'pending'  => 'pending',
                    $payment->status === 'rejected' => 'due',
                    $payment->status === 'approved' && $payment->valid_until && $payment->valid_until->toDateString() >= $todayStr => 'active',
                    default => 'expired',
                };
            } else {
                $status = $isPastOrCurrentMonth ? 'due' : 'upcoming';
            }

            return [
                'passenger' => $p,
                'payment'   => $payment,
                'status'    => $status,
            ];
        });

        $counts = [
            'active'   => $records->where('status', 'active')->count(),
            'due'      => $records->where('status', 'due')->count(),
            'upcoming' => $records->where('status', 'upcoming')->count(),
            'pending'  => $records->where('status', 'pending')->count(),
            'expired'  => $records->where('status', 'expired')->count(),
        ];

        $monthLabel = Carbon::createFromFormat('Y-m', $month)->format('F Y');

        return view('fee-payments.index', compact(
            'setting', 'pendingPayments', 'records', 'counts', 'month', 'monthLabel'
        ));
    }

    // Update the monthly fee amount
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'monthly_fee'      => 'required|numeric|min:0|max:999999',
            'jazzcash_number'  => 'sometimes|nullable|string|max:50',
            'easypaisa_number' => 'sometimes|nullable|string|max:50',
        ]);

        $setting = FeeSetting::current();
        $updates = [
            'monthly_fee' => $validated['monthly_fee'],
            'updated_by'  => Auth::id(),
        ];

        // Preserve previously configured wallet details when an older client
        // submits only the monthly fee. Empty fields from the Admin form still
        // intentionally clear the corresponding number.
        if ($request->exists('jazzcash_number')) {
            $updates['jazzcash_number'] = filled($validated['jazzcash_number'] ?? null)
                ? trim($validated['jazzcash_number'])
                : null;
        }
        if ($request->exists('easypaisa_number')) {
            $updates['easypaisa_number'] = filled($validated['easypaisa_number'] ?? null)
                ? trim($validated['easypaisa_number'])
                : null;
        }

        $setting->update($updates);

        return redirect()->back()->with('success', 'Transport fee and payment details updated.');
    }

    // Approve a fee payment — extends the passenger's pass validity through the paid month.
    public function approve(FeePayment $feePayment)
    {
        $feePayment->update([
            'status'            => 'approved',
            'valid_until'       => Carbon::createFromFormat('Y-m', $feePayment->month)->endOfMonth(),
            'approved_by'       => Auth::id(),
            'approved_at'       => now(),
            'rejection_reason'  => null,
        ]);

        return redirect()->back()->with('success',
            "{$feePayment->passenger->name}'s payment for {$feePayment->monthLabel()} has been approved. Their transport pass is now active.");
    }

    // Reject a fee payment (also revokes the validity window it would have granted)
    public function reject(Request $request, FeePayment $feePayment)
    {
        $request->validate([
            'rejection_reason' => 'nullable|string|max:255',
        ]);

        $feePayment->update([
            'status'            => 'rejected',
            'rejection_reason'  => $request->rejection_reason,
            'valid_until'       => null,
        ]);

        return redirect()->back()->with('success',
            "{$feePayment->passenger->name}'s payment for {$feePayment->monthLabel()} has been rejected.");
    }

    /*
    |--------------------------------------------------------------------
    | PASSENGER SELF-SERVICE
    |--------------------------------------------------------------------
    */

    // "My Transport Fee" page — current status + pay form
    public function myFee()
    {
        $passenger = Passenger::where('user_id', Auth::id())->firstOrFail();

        if (!$passenger->isApproved()) {
            return redirect()->route('dashboard')->with('error',
                'Your transport application must be approved before you can pay the transport fee.');
        }

        $active  = $passenger->activeFeePayment();
        $pending = $passenger->pendingFeePayment();
        $history = $passenger->feePayments()->latest('month')->get();

        $canPay     = !$pending;
        $targetMonth = $this->nextPayableMonth($passenger);

        $setting = FeeSetting::current();
        $amount = (float) $setting->monthly_fee;

        $stripe = new StripeClient(config('services.stripe.secret'));

        return view('fee-payments.my', compact(
            'passenger', 'active', 'pending', 'history', 'canPay', 'targetMonth', 'amount', 'setting'
        ))->with('stripeEnabled', $stripe->isConfigured())
          ->with('stripeCurrency', strtoupper(config('services.stripe.currency')));
    }

    // Passenger submits a payment (TID + screenshot) for the next payable month
    public function pay(Request $request)
    {
        $passenger = Passenger::where('user_id', Auth::id())->firstOrFail();

        if (!$passenger->isApproved()) {
            return redirect()->route('dashboard')->with('error',
                'Your transport application must be approved before you can pay the transport fee.');
        }

        if ($passenger->pendingFeePayment()) {
            return back()->with('error', 'You already have a payment awaiting review. Please wait for the admin to approve or reject it before submitting another.');
        }

        $validated = $request->validate([
            'payment_method' => 'nullable|string|in:manual,jazzcash,easypaisa',
            'tid'            => 'required|string|max:100',
            'screenshot'     => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);
        $paymentMethod = $validated['payment_method'] ?? 'manual';

        $setting = FeeSetting::current();
        if ($paymentMethod === 'jazzcash' && blank($setting->jazzcash_number)) {
            return back()->withInput()->with('error', 'JazzCash is not configured right now. Please choose another payment option.');
        }
        if ($paymentMethod === 'easypaisa' && blank($setting->easypaisa_number)) {
            return back()->withInput()->with('error', 'Easypaisa is not configured right now. Please choose another payment option.');
        }

        $month = $this->nextPayableMonth($passenger);
        // Bank transaction screenshots are sensitive — private disk only,
        // served through the authenticated showScreenshot() route below.
        $path  = $request->file('screenshot')->store('fee-screenshots', 'local');

        FeePayment::create([
            'passenger_id'     => $passenger->id,
            'month'            => $month,
            'amount'           => FeeSetting::amount(),
            'tid'              => $validated['tid'],
            'screenshot_path'  => $path,
            'status'           => 'pending',
            'payment_method'   => $paymentMethod,
        ]);

        return redirect()->route('fee.my')->with('success',
            'Your payment has been submitted for review. You will be able to see your active pass here once the admin approves it.');
    }

    // ── Stream a fee-payment screenshot from the private disk ──
    // Staff (admin/incharge) can view any payment's screenshot. A passenger
    // can only view their own — enforced here, not just by obscure filenames.
    public function showScreenshot(FeePayment $feePayment)
    {
        $user = Auth::user();
        $isStaff = in_array($user->role, ['admin', 'incharge'], true);
        $isOwner = $feePayment->passenger && $feePayment->passenger->user_id === $user->id;

        abort_unless($isStaff || $isOwner, 403);
        abort_if(!$feePayment->screenshot_path, 404);
        abort_unless(\Illuminate\Support\Facades\Storage::disk('local')->exists($feePayment->screenshot_path), 404);

        return \Illuminate\Support\Facades\Storage::disk('local')->response($feePayment->screenshot_path);
    }

    /*
    |--------------------------------------------------------------------
    | STRIPE — sandbox/test-mode card payment (alternative to manual
    | bank-transfer + screenshot). Uses Stripe Checkout: we create a
    | session, redirect the passenger to Stripe's hosted payment page,
    | and confirm the result by re-fetching the session on return (no
    | public webhook endpoint required for this to work correctly).
    |--------------------------------------------------------------------
    */

    // Passenger: start a Stripe Checkout session for the next payable month
    public function stripeCheckout(Request $request)
    {
        $passenger = Passenger::where('user_id', Auth::id())->firstOrFail();

        if (!$passenger->isApproved()) {
            return redirect()->route('dashboard')->with('error',
                'Your transport application must be approved before you can pay the transport fee.');
        }

        if ($passenger->pendingFeePayment()) {
            return back()->with('error', 'You already have a payment awaiting review. Please wait for it to be resolved before paying again.');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        if (!$stripe->isConfigured()) {
            return back()->with('error', 'Card payment is not available right now. Please use the bank transfer method below.');
        }

        $month    = $this->nextPayableMonth($passenger);
        $currency = strtolower(config('services.stripe.currency', 'usd'));
        // The amount charged to the card always matches FeeSetting::amount() —
        // the same figure shown everywhere else in the app — converted to
        // Stripe's required smallest currency unit (e.g. cents), never a
        // hardcoded or separately-maintained value.
        $amount   = FeeSetting::amount();

        $session = $stripe->createCheckoutSession([
            'mode'                                  => 'payment',
            'payment_method_types'                  => ['card'],
            'success_url'                            => route('fee.stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'                              => route('fee.stripe.cancel') . '?session_id={CHECKOUT_SESSION_ID}',
            'client_reference_id'                     => (string) $passenger->id,
            'customer_email'                          => $passenger->user->email ?? null,
            'line_items' => [[
                'price_data' => [
                    'currency'     => $currency,
                    'unit_amount'  => $stripe->toSmallestUnit($amount, $currency),
                    'product_data' => [
                        'name' => "Transport Fee — " . Carbon::createFromFormat('Y-m', $month)->format('F Y'),
                    ],
                ],
                'quantity' => 1,
            ]],
        ]);

        // Reserve the record now so we know which passenger/month a returning
        // session_id belongs to, even if the browser never redirects back.
        FeePayment::create([
            'passenger_id'                => $passenger->id,
            'month'                       => $month,
            'amount'                      => $amount,
            'currency'                    => $currency,
            'payment_method'              => 'stripe',
            'status'                      => 'pending',
            'stripe_checkout_session_id'  => $session['id'],
        ]);

        return redirect($session['url']);
    }

    // Stripe redirects here after a successful checkout — confirm with Stripe, then activate
    public function stripeSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');
        abort_if(!$sessionId, 400);

        $payment = FeePayment::where('stripe_checkout_session_id', $sessionId)->firstOrFail();

        // Only the passenger who owns this payment (or staff) may land here
        $isOwner = $payment->passenger && $payment->passenger->user_id === Auth::id();
        abort_unless($isOwner || in_array(Auth::user()->role, ['admin', 'incharge'], true), 403);

        $stripe  = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->retrieveCheckoutSession($sessionId);

        if (($session['payment_status'] ?? null) !== 'paid') {
            return redirect()->route('fee.my')->with('error',
                'Your payment could not be confirmed yet. If you completed checkout, please wait a moment and refresh — otherwise no charge was made.');
        }

        $payment->update([
            'status'                     => 'approved',
            'stripe_payment_intent_id'   => $session['payment_intent'] ?? null,
            'valid_until'                => Carbon::createFromFormat('Y-m', $payment->month)->endOfMonth(),
            'approved_at'                => now(),
        ]);

        return redirect()->route('fee.my')->with('success',
            'Payment successful! Your transport pass is now active for ' . $payment->monthLabel() . '.');
    }

    // Stripe redirects here if the passenger cancels/abandons checkout
    public function stripeCancel(Request $request)
    {
        $sessionId = $request->query('session_id');

        // Clean up the reserved pending record so an abandoned checkout
        // doesn't block the passenger from paying again.
        if ($sessionId) {
            FeePayment::where('stripe_checkout_session_id', $sessionId)
                ->where('status', 'pending')
                ->where('payment_method', 'stripe')
                ->delete();
        }

        return redirect()->route('fee.my')->with('error', 'Card payment was cancelled. No charge was made — you can try again or pay via bank transfer below.');
    }

    /*
    |--------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------
    */

    // The next calendar month a passenger is allowed to submit a payment for.
    private function nextPayableMonth(Passenger $passenger): string
    {
        $currentMonth = now()->format('Y-m');

        $active = $passenger->activeFeePayment();
        if ($active && $active->month >= $currentMonth) {
            // Already covered for this month (or paid ahead) — offer the following month
            return Carbon::createFromFormat('Y-m', $active->month)->addMonth()->format('Y-m');
        }

        return $currentMonth;
    }
}
