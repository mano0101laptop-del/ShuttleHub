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

   
    public function Stops(): HasMany
    {
        return $this->hasMany(Stop::class)->orderBy('sequence');
    }

 
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'route_id');
    }

    
    public function syncStopsCount(): void
    {
        $this->update(['stops' => max($this->Stops()->count(), 1)]);
    }
}
