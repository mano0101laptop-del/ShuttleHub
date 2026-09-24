<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    protected $fillable = ['name', 'from', 'to', 'stops', 'status', 'vehicle_id'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    /** Ordered list of stops belonging to this route. */
    public function Stops(): HasMany
    {
        return $this->hasMany(Stop::class)->orderBy('sequence');
    }

    /** Persistent operational schedules (driver/vehicle/timing) for this route. */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'route_id');
    }

    /**
     * Keep the legacy `stops` integer column (a simple count, still used by
     * older parts of the UI) in sync with the real number of stop records,
     * so nothing that reads `stops` as a count breaks.
     */
    public function syncStopsCount(): void
    {
        $this->update(['stops' => max($this->Stops()->count(), 1)]);
    }
}
