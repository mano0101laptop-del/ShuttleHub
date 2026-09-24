<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name', 'phone', 'license', 'cnic', 'dob', 'address', 'email',
        'experience', 'status', 'vehicle_id',
        'photo_path', 'cnic_front_path', 'cnic_back_path',
        'reference_name', 'reference_phone', 'reference_relation', 'reference_address',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** The login account issued to this driver so they can use the driver portal. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'driver_id');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    public function complaintsAgainst(): HasMany
    {
        return $this->hasMany(Complaint::class, 'against_driver_id');
    }
}
