<?php

namespace App\Http\Controllers;

use App\Services\Stripe\StripeClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


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
