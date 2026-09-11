<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds Stripe Checkout support alongside the existing manual (TID +
 * screenshot) payment method.
 *
 * `payment_method` distinguishes the two flows:
 *   manual -> tid + screenshot_path are required, admin approves by hand
 *   stripe -> tid/screenshot are not used; stripe_session_id +
 *             stripe_payment_intent identify the charge, and the payment
 *             is auto-approved the moment Stripe confirms it (either via
 *             the success redirect or the webhook, whichever lands first)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->string('payment_method')->default('manual')->after('amount');
            $table->string('stripe_session_id')->nullable()->unique()->after('screenshot_path');
            $table->string('stripe_payment_intent')->nullable()->after('stripe_session_id');
        });

        // Manual-only fields are no longer required once Stripe payments exist.
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->string('tid')->nullable()->change();
            $table->string('screenshot_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'stripe_session_id', 'stripe_payment_intent']);
            $table->string('tid')->nullable(false)->change();
            $table->string('screenshot_path')->nullable(false)->change();
        });
    }
};
