<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Tiny wrapper around Stripe's REST API for Checkout Sessions.
 *
 * Deliberately dependency-free (no stripe/stripe-php package) — talks to
 * https://api.stripe.com directly over HTTP with the secret key as the
 * Basic Auth username, exactly as Stripe's API expects. This keeps Stripe
 * support usable without a Composer install step.
 */
class StripeClient
{
    private const BASE_URL = 'https://api.stripe.com/v1';

    
    private const ZERO_DECIMAL_CURRENCIES = [
        'bif', 'clp', 'djf', 'gnf', 'jpy', 'kmf', 'krw', 'mga', 'pyg',
        'rwf', 'ugx', 'vnd', 'vuv', 'xaf', 'xof', 'xpf',
    ];

    public function __construct(private readonly ?string $secretKey)
    {
    }

    public function isConfigured(): bool
    {
        return !empty($this->secretKey);
    }

   
    public function toSmallestUnit(float $amount, string $currency): int
    {
        $currency = strtolower($currency);

        if (in_array($currency, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return (int) round($amount);
        }

        return (int) round($amount * 100);
    }

    
    public function createCheckoutSession(array $params): array
    {
        $this->assertConfigured();

        $response = Http::asForm()
            ->withBasicAuth($this->secretKey, '')
            ->baseUrl(self::BASE_URL)
            ->post('/checkout/sessions', $params);

        if ($response->failed()) {
            throw new RuntimeException(
                'Stripe checkout session creation failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        return $response->json();
    }

   
    public function retrieveCheckoutSession(string $sessionId): array
    {
        $this->assertConfigured();

        $response = Http::withBasicAuth($this->secretKey, '')
            ->baseUrl(self::BASE_URL)
            ->get("/checkout/sessions/{$sessionId}");

        if ($response->failed()) {
            throw new RuntimeException(
                'Stripe checkout session lookup failed: ' . ($response->json('error.message') ?? $response->body())
            );
        }

        return $response->json();
    }

    private function assertConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new RuntimeException('Stripe secret key is not configured (STRIPE_SECRET).');
        }
    }
}
