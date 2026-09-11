<?php

namespace App\Services\Stripe;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * A deliberately tiny wrapper around the Stripe REST API.
 *
 * We talk to https://api.stripe.com directly over HTTPS instead of pulling
 * in the stripe-php composer package — Stripe's API is plain form-encoded
 * REST, so a full SDK isn't needed for the handful of calls Shuttle Hub
 * makes (create a Checkout Session, look one up, and verify a webhook
 * signature). This also keeps the project's dependency footprint small,
 * which matters on a XAMPP/local composer setup.
 */
class StripeClient
{
    private string $secretKey;
    private string $baseUrl = 'https://api.stripe.com/v1';

    public function __construct(?string $secretKey = null)
    {
        $this->secretKey = $secretKey ?? (string) config('services.stripe.secret');

        if ($this->secretKey === '') {
            throw new RuntimeException('Stripe secret key is not configured. Set STRIPE_SECRET in your .env file.');
        }
    }

    /**
     * Create a Stripe Checkout Session for a one-off card payment and
     * return the decoded response (contains `id` and `url`).
     */
    public function createCheckoutSession(array $params): array
    {
        return $this->request('post', '/checkout/sessions', $params);
    }

    /** Fetch a Checkout Session by id (used on the success redirect). */
    public function retrieveCheckoutSession(string $sessionId, array $expand = []): array
    {
        $query = [];
        foreach ($expand as $i => $field) {
            $query["expand[{$i}]"] = $field;
        }

        return $this->request('get', "/checkout/sessions/{$sessionId}", $query);
    }

    private function request(string $method, string $path, array $params = []): array
    {
        $response = Http::withBasicAuth($this->secretKey, '')
            ->asForm()
            ->withOptions($this->sslOptions())
            ->{$method}($this->baseUrl.$path, $params);

        if ($response->failed()) {
            $message = $response->json('error.message') ?? 'Unknown Stripe API error.';
            throw new RuntimeException("Stripe API error: {$message}");
        }

        return $response->json();
    }

    /**
     * cURL options for the SSL handshake. On a lot of Windows/XAMPP setups
     * PHP's cURL build doesn't know where the system's root CA store is,
     * which makes every outbound HTTPS call fail with "unable to get local
     * issuer certificate" — that's a PHP/OS config issue, not something
     * wrong with the request itself.
     *
     * In `local` environment (APP_ENV=local) we always skip certificate
     * verification for calls made through this client — this is what
     * makes local development work out of the box on Windows/XAMPP/
     * LaraStack without needing a CA bundle at all (a downloaded
     * cacert.pem can itself end up incomplete/corrupted depending on how
     * it was saved, which is a dead end to debug on someone else's
     * machine). This fallback NEVER activates outside `local` — see the
     * environment check below — so a real deployment always verifies
     * normally.
     *
     * Proper permanent fix, if you want it (fixes ALL outbound HTTPS
     * calls system-wide, not just Stripe): download
     * https://curl.se/ca/cacert.pem, then in php.ini set
     *   curl.cainfo = "C:\path\to\cacert.pem"
     *   openssl.cafile = "C:\path\to\cacert.pem"
     * and restart `php artisan serve`. Not required for this app to work.
     */
    private function sslOptions(): array
    {
        if (app()->environment('local')) {
            return ['verify' => false];
        }

        $bundle = config('services.stripe.ca_bundle');

        if ($bundle && is_string($bundle) && file_exists($bundle)) {
            return ['verify' => $bundle];
        }

        return [];
    }

    /**
     * Verify a `Stripe-Signature` webhook header against the raw request
     * body, mirroring what stripe-php's Webhook::constructEvent() does,
     * without requiring the SDK. Returns true only if at least one
     * timestamped v1 signature matches within a 5 minute tolerance.
     */
    public static function verifyWebhookSignature(string $payload, ?string $sigHeader, string $endpointSecret, int $tolerance = 300): bool
    {
        if (!$sigHeader) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $sigHeader) as $piece) {
            [$key, $value] = array_pad(explode('=', $piece, 2), 2, null);
            $parts[$key][] = $value;
        }

        $timestamp = $parts['t'][0] ?? null;
        $signatures = $parts['v1'] ?? [];

        if (!$timestamp || empty($signatures)) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$payload}", $endpointSecret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, (string) $signature)) {
                return true;
            }
        }

        return false;
    }
}
