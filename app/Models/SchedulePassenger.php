<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchedulePassenger extends Model
{
    protected $table = 'schedule_passengers';

    protected $fillable = [
        'schedule_id', 'passenger_id', 'stop_id',
        'pickup_time', 'dropoff_time',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function Stop(): BelongsTo
    {
        return $this->belongsTo(Stop::class);
    }
}
