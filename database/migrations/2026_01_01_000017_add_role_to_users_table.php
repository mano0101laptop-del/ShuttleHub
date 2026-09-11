<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // nullable first so existing rows don't violate NOT NULL during ALTER
            $table->string('role')->nullable()->after('email');
        });

        // Any user that already exists before this migration is assumed to be an admin
        // (they were manually created / seeded before the role system existed).
        // New users created via the register form will have their role set explicitly.
        DB::table('users')->whereNull('role')->update(['role' => 'admin']);

        // Now make it non-nullable with a safe default for future inserts
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('passenger')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
