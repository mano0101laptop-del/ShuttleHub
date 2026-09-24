<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    /**
     * Keep the existing table for backwards compatibility. The application
     * now treats the latest row for each route as the persistent Schedule.
     */
    protected $table = 'schedules';

    protected $fillable = [
        'date', 'route_id', 'driver_id', 'vehicle_id',
        'estimated_passengers', 'estimated_departure_time',
        'notes', 'status', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Persistent schedule rows: one current row per route. Historical rows
     * from the previous date-based implementation are ignored.
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->whereIn('schedules.id', function ($subquery) {
            $subquery->from('schedules')
                ->selectRaw('MAX(id)')
                ->groupBy('route_id');
        });
    }

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
        return $this->hasMany(ScheduleStop::class, 'schedule_id');
    }

    public function passengerAssignments(): HasMany
    {
        return $this->hasMany(SchedulePassenger::class, 'schedule_id');
    }

    /** Ordered route stops merged with the saved Schedule pickup/drop overrides. */
    public function stopsWithTimes()
    {
        $overrides = $this->stops->keyBy('stop_id');

        return $this->route->Stops->map(function (Stop $stop) use ($overrides) {
            $override = $overrides->get($stop->id);
            $pickup = $override?->pickup_time ?? $override?->estimated_time ?? $stop->eta;

            return [
                'stop' => $stop,
                // Backwards-compatible key used by the passenger dashboard.
                'time' => $pickup,
                'pickup_time' => $pickup,
                'dropoff_time' => $override?->dropoff_time,
                'selected' => (bool) $override,
            ];
        });
    }
}