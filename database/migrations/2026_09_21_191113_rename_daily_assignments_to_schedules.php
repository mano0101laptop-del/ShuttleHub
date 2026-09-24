<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('daily_assignments', 'schedules');
        Schema::rename('daily_assignment_stops', 'schedule_stops');
        Schema::rename('daily_assignment_passengers', 'schedule_passengers');

        Schema::table('schedule_stops', function (Blueprint $table) {
            $table->renameColumn('daily_assignment_id', 'schedule_id');
        });

        Schema::table('schedule_passengers', function (Blueprint $table) {
            $table->renameColumn('daily_assignment_id', 'schedule_id');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_passengers', function (Blueprint $table) {
            $table->renameColumn('schedule_id', 'daily_assignment_id');
        });

        Schema::table('schedule_stops', function (Blueprint $table) {
            $table->renameColumn('schedule_id', 'daily_assignment_id');
        });

        Schema::rename('schedule_passengers', 'daily_assignment_passengers');
        Schema::rename('schedule_stops', 'daily_assignment_stops');
        Schema::rename('schedules', 'daily_assignments');
    }
};