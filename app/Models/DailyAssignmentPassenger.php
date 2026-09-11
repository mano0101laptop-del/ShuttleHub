<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAssignmentPassenger extends Model
{
    protected $fillable = [
        'daily_assignment_id', 'passenger_id', 'route_stop_id',
        'pickup_time', 'dropoff_time',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(DailyAssignment::class, 'daily_assignment_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class);
    }
}
