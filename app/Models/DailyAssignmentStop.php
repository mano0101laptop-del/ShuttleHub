<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyAssignmentStop extends Model
{
    protected $fillable = [
        'daily_assignment_id', 'route_stop_id', 'estimated_time',
        'pickup_time', 'dropoff_time',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(DailyAssignment::class, 'daily_assignment_id');
    }

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class);
    }
}
