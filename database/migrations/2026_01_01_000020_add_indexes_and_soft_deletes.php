<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Soft deletes: previously hard-deleting a Passenger cascaded and
        // permanently destroyed their entire attendance history. Passenger,
        // Driver and Vehicle models now use SoftDeletes — a "delete" sets
        // deleted_at instead of running a real DELETE, so the existing
        // cascadeOnDelete foreign key on attendances.passenger_id never
        // fires and attendance records are preserved for audit purposes.
        Schema::table('passengers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('drivers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Indexes for the columns every dashboard/report/attendance query
        // filters on, which previously had no index at all.
        Schema::table('passengers', function (Blueprint $table) {
            $table->index('status');
            $table->index('approval_status');
        });

        // Guarantee at the database layer that a passenger can only ever
        // have one attendance row per day (the app already relies on this
        // via updateOrCreate([passenger_id,date]), but nothing previously
        // enforced it if two requests raced or a future code path forgot
        // the updateOrCreate pattern).
        //
        // NOTE: if you are migrating an existing database that may already
        // contain duplicate (passenger_id, date) rows, de-duplicate them
        // before running this migration or it will fail.
        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(['passenger_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['passenger_id', 'date']);
        });

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
