<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->string('cancellation_status')->default('none')->after('approval_status'); // none, requested, approved, rejected
            $table->string('cancellation_reason')->nullable()->after('cancellation_status');
            $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropColumn(['cancellation_status', 'cancellation_reason', 'cancellation_requested_at']);
        });
    }
};
