<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->string('qr_token')->unique()->nullable()->after('roll');
            $table->string('fingerprint_hash')->nullable()->after('qr_token');
            $table->boolean('fingerprint_enrolled')->default(false)->after('fingerprint_hash');
        });
    }

    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'fingerprint_hash', 'fingerprint_enrolled']);
        });
    }
};
