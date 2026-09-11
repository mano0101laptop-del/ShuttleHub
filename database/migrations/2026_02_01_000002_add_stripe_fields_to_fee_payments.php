<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            // Existing manual (bank transfer + screenshot) flow keeps using
            // tid/screenshot_path — both become nullable because a Stripe
            // payment never fills either of them.
            $table->string('tid')->nullable()->change();
            $table->string('screenshot_path')->nullable()->change();

            // payment_method already exists (added by
            // 2026_01_01_000028_add_stripe_fields_to_fee_payments_table) —
            // only add the columns the app actually uses that are still missing.
            $table->string('stripe_checkout_session_id')->nullable()->unique()->after('payment_method');
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            $table->string('currency', 3)->default('pkr')->after('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropColumn(['stripe_checkout_session_id', 'stripe_payment_intent_id', 'currency']);
            $table->string('tid')->nullable(false)->change();
            $table->string('screenshot_path')->nullable(false)->change();
        });
    }
};
