<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = ['driver_user_id', 'sender_id', 'body', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /** The driver-role user this thread belongs to. */
    public function driverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    /** Whoever actually wrote this particular message (driver or admin/incharge). */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function isFromDriver(): bool
    {
        return $this->sender_id === $this->driver_user_id;
    }
}
