<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = ['passenger_id', 'date', 'status', 'time', 'method'];

    protected $casts = [
        'date' => 'date:Y-m-d',
    ];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }
}
