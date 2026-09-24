<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleStop extends Model
{
    protected $table = 'schedule_stops';

    protected $fillable = [
        'schedule_id', 'stop_id', 'estimated_time',
        'pickup_time', 'dropoff_time',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function Stop(): BelongsTo
    {
        return $this->belongsTo(Stop::class);
    }
}
