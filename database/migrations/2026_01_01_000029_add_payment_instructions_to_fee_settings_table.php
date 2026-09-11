<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The manual (TID) payment flow asked passengers for a transaction ID
 * without ever telling them WHERE to send the money in the first place —
 * there was no admin-configurable bank/JazzCash/EasyPaisa account anywhere
 * in the app. This adds a single free-text field admin fills in once
 * (account title, number, bank name — whatever combination applies) and
 * that gets shown to passengers right above the "Submit for Review" form.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_settings', function (Blueprint $table) {
            $table->text('payment_instructions')->nullable()->after('monthly_fee');
        });
    }

    public function down(): void
    {
        Schema::table('fee_settings', function (Blueprint $table) {
            $table->dropColumn('payment_instructions');
        });
    }
};
