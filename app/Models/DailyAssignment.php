<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyAssignment extends Model
{
    protected $fillable = [
        'date', 'route_id', 'driver_id', 'vehicle_id',
        'estimated_passengers', 'estimated_departure_time',
        'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stops(): HasMany
    {
        return $this->hasMany(DailyAssignmentStop::class);
    }

    public function passengerAssignments(): HasMany
    {
        return $this->hasMany(DailyAssignmentPassenger::class);
    }

    /** Ordered route stops merged with this day's pickup/drop overrides. */
    public function stopsWithTimes()
    {
        $overrides = $this->stops->keyBy('route_stop_id');

        return $this->route->routeStops->map(function (RouteStop $stop) use ($overrides) {
            $override = $overrides->get($stop->id);
            $pickup = $override?->pickup_time ?? $override?->estimated_time ?? $stop->eta;

            return [
                'stop' => $stop,
                // Backwards-compatible key used by the scanner and passenger dashboard.
                'time' => $pickup,
                'pickup_time' => $pickup,
                'dropoff_time' => $override?->dropoff_time,
                'selected' => (bool) $override,
            ];
        });
    }
}
