<?php

namespace App\Http\Controllers;

use App\Services\Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives Stripe webhook events. This is a safety net, not the primary
 * flow — the success-redirect in FeePaymentController::stripeSuccess()
 * already confirms and finalizes most payments the moment the passenger
 * is bounced back from Checkout. The webhook exists to catch the case
 * where a passenger pays but closes the tab before the redirect
 * completes.
 *
 * NOTE for local/XAMPP testing: Stripe needs a publicly reachable URL to
 * deliver webhooks, so this endpoint will only fire against localhost if
 * you tunnel it (e.g. `stripe listen --forward-to
 * localhost/stripe/webhook`) or use the Stripe CLI. It is not required
 * for the demo to work — the success redirect alone is enough.
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = (string) config('services.stripe.webhook_secret');

        if ($secret !== '' && !StripeClient::verifyWebhookSignature($payload, $sigHeader, $secret)) {
            Log::warning('Stripe webhook signature verification failed.');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        $type  = $event['type'] ?? null;

        if ($type === 'checkout.session.completed' || $type === 'checkout.session.async_payment_succeeded') {
            $session = $event['data']['object'] ?? [];

            if (($session['payment_status'] ?? null) === 'paid' && !empty($session['id'])) {
                app(FeePaymentController::class)->recordConfirmedStripePayment($session);
            }
        }

        return response()->json(['received' => true]);
    }
}
