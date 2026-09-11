<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $fillable = [
        'passenger_id', 'type', 'against_type',
        'against_driver_id', 'against_passenger_id',
        'subject', 'message', 'status', 'admin_response',
    ];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function againstDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'against_driver_id');
    }

    public function againstPassenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'against_passenger_id');
    }

    public function isFeedback(): bool { return $this->type === 'feedback'; }
    public function isComplaint(): bool { return $this->type === 'complaint'; }
    public function isOpen(): bool { return $this->status === 'open'; }
    public function isResolved(): bool { return $this->status === 'resolved'; }
}
