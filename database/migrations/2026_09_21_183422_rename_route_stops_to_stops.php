<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('route_stops', 'stops');

        Schema::table('passengers', function (Blueprint $table) {
            $table->renameColumn('route_stop_id', 'stop_id');
        });

        Schema::table('daily_assignment_stops', function (Blueprint $table) {
            $table->renameColumn('route_stop_id', 'stop_id');
        });

        Schema::table('daily_assignment_passengers', function (Blueprint $table) {
            $table->renameColumn('route_stop_id', 'stop_id');
        });
    }

    public function down(): void
    {
        Schema::table('daily_assignment_passengers', function (Blueprint $table) {
            $table->renameColumn('stop_id', 'route_stop_id');
        });

        Schema::table('daily_assignment_stops', function (Blueprint $table) {
            $table->renameColumn('stop_id', 'route_stop_id');
        });

        Schema::table('passengers', function (Blueprint $table) {
            $table->renameColumn('stop_id', 'route_stop_id');
        });

        Schema::rename('stops', 'route_stops');
    }
};