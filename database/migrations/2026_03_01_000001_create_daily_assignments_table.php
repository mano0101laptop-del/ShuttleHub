<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-day operational assignment: lets the Transport Incharge lock in
     * exactly which driver/vehicle runs a route on a given date, along with
     * a day-specific estimated passenger count and departure time — since
     * these can change day to day (a driver on leave, a bus swapped in,
     * extra passengers expected, traffic delays, etc.) without touching the
     * route's permanent/default configuration in `routes`/`route_stops`.
     */
    public function up(): void
    {
        Schema::create('daily_assignments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->unsignedInteger('estimated_passengers')->nullable();
            $table->string('estimated_departure_time')->nullable(); // free-text, e.g. "07:30 AM"
            $table->text('notes')->nullable(); // e.g. "Heavy traffic expected on Mall Road"
            $table->string('status')->default('Scheduled'); // Scheduled, Completed, Cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['date', 'route_id'], 'daily_assignment_date_route_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_assignments');
    }
};
