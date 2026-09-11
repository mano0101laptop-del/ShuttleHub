<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Day-specific stop timing for a daily_assignment. Defaults to the
     * route's normal stop ETA, but the Incharge can override any stop's
     * time for that particular day (e.g. running late because of traffic)
     * without changing the route's permanent stop schedule.
     */
    public function up(): void
    {
        Schema::create('daily_assignment_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_assignment_id')->constrained('daily_assignments')->cascadeOnDelete();
            $table->foreignId('route_stop_id')->constrained('route_stops')->cascadeOnDelete();
            $table->string('estimated_time')->nullable(); // e.g. "07:52 AM" — overrides route_stops.eta for this day
            $table->timestamps();

            $table->unique(['daily_assignment_id', 'route_stop_id'], 'daily_assignment_stop_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_assignment_stops');
    }
};
