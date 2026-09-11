<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('id');
            // nullable first so existing rows don't break
            $table->string('approval_status')->nullable()->after('status');
        });

        // All existing passengers (created by admin before this migration) are already approved
        DB::table('passengers')->whereNull('approval_status')->update(['approval_status' => 'approved']);

        // Make non-nullable with default for future rows
        Schema::table('passengers', function (Blueprint $table) {
            $table->string('approval_status')->default('pending')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'approval_status']);
        });
    }
};
