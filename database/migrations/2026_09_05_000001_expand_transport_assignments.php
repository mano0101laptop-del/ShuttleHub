<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->foreignId('route_stop_id')->nullable()->after('route_id')->constrained('route_stops')->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->after('route_stop_id')->constrained('drivers')->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->after('driver_id')->constrained('vehicles')->nullOnDelete();
            $table->string('pickup_time', 50)->nullable()->after('vehicle_id');
            $table->string('dropoff_time', 50)->nullable()->after('pickup_time');
        });

        Schema::table('daily_assignment_stops', function (Blueprint $table) {
            $table->string('pickup_time', 50)->nullable()->after('estimated_time');
            $table->string('dropoff_time', 50)->nullable()->after('pickup_time');
        });

        Schema::create('daily_assignment_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_assignment_id')->constrained('daily_assignments')->cascadeOnDelete();
            $table->foreignId('passenger_id')->constrained('passengers')->cascadeOnDelete();
            $table->foreignId('route_stop_id')->nullable()->constrained('route_stops')->nullOnDelete();
            $table->string('pickup_time', 50)->nullable();
            $table->string('dropoff_time', 50)->nullable();
            $table->timestamps();

            $table->unique(['daily_assignment_id', 'passenger_id'], 'daily_assignment_passenger_unique');
            $table->index(['daily_assignment_id', 'route_stop_id'], 'daily_assignment_passenger_stop_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_assignment_passengers');

        Schema::table('daily_assignment_stops', function (Blueprint $table) {
            $table->dropColumn(['pickup_time', 'dropoff_time']);
        });

        Schema::table('passengers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('route_stop_id');
            $table->dropConstrainedForeignId('driver_id');
            $table->dropConstrainedForeignId('vehicle_id');
            $table->dropColumn(['pickup_time', 'dropoff_time']);
        });
    }
};
