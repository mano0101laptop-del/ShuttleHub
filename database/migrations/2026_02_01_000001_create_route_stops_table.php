<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('routes')->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('sequence')->default(1);
            $table->string('eta')->nullable(); // free-text ETA / scheduled time, e.g. "07:45 AM"
            $table->timestamps();

            $table->index(['route_id', 'sequence']);
        });

        // Backfill: for any existing route, seed a single stop row using its
        // current "to" destination so routes created before this feature
        // still show at least one stop and nothing appears broken.
        $routes = \DB::table('routes')->select('id', 'to')->get();
        foreach ($routes as $route) {
            \DB::table('route_stops')->insert([
                'route_id'   => $route->id,
                'name'       => $route->to ?: 'Stop 1',
                'sequence'   => 1,
                'eta'        => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('route_stops');
    }
};
