<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_settings', function (Blueprint $table) {
            $table->string('jazzcash_number', 50)->nullable()->after('payment_instructions');
            $table->string('easypaisa_number', 50)->nullable()->after('jazzcash_number');
        });
    }

    public function down(): void
    {
        Schema::table('fee_settings', function (Blueprint $table) {
            $table->dropColumn(['jazzcash_number', 'easypaisa_number']);
        });
    }
};
