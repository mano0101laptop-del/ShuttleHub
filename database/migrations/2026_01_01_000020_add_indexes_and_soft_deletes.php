<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Soft deletes: previously hard-deleting a Passenger permanently
        // destroyed related records. Passenger, Driver and Vehicle models
        // now use SoftDeletes — a "delete" sets deleted_at instead of
        // running a real DELETE, preserving history for audit purposes.
        Schema::table('passengers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('drivers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Indexes for the columns every dashboard/report query filters on,
        // which previously had no index at all.
        Schema::table('passengers', function (Blueprint $table) {
            $table->index('status');
            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['approval_status']);
            $table->dropSoftDeletes();
        });
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
