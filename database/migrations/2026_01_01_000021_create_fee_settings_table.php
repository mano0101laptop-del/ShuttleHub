<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Singleton-style table (always a single row, id = 1) holding the current
 * monthly transport fee amount the admin has configured. Kept separate from
 * fee_payments so the fee amount can change over time without touching
 * historical payment records (each payment stores its own `amount` too).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_settings');
    }
};
