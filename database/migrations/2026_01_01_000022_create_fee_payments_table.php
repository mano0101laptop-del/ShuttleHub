<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per monthly fee submission from a passenger.
 *
 * Lifecycle:
 *   pending  -> passenger uploaded a payment screenshot + TID for a month
 *   approved -> admin verified it; the pass is valid until valid_until
 *               (end of the paid month)
 *   rejected -> admin declined it (see rejection_reason); passenger must
 *               resubmit
 *
 * `month` is stored as a 'YYYY-MM' string so a passenger can have at most
 * one meaningful payment per calendar month (enforced in the controller,
 * not a hard DB unique, so a rejected submission can be resubmitted).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->constrained()->cascadeOnDelete();

            $table->string('month', 7); // e.g. "2026-07"
            $table->decimal('amount', 10, 2);

            $table->string('tid');
            $table->string('screenshot_path');

            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('rejection_reason')->nullable();

            $table->date('valid_until')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->index(['passenger_id', 'month']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
