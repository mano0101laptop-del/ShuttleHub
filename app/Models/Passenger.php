<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Passenger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name', 'roll', 'contact_number', 'passenger_type', 'department', 'address',
        'photo_path', 'emergency_contact', 'stop', 'status',
        'approval_status',
        'cancellation_status', 'cancellation_reason', 'cancellation_requested_at',
        'route_id', 'stop_id', 'driver_id', 'vehicle_id', 'pickup_time', 'dropoff_time',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function Stop(): BelongsTo
    {
        return $this->belongsTo(Stop::class);
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function assignedVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    /** The most recent fee payment that is currently approved & not expired (if any). */
    public function activeFeePayment(): ?FeePayment
    {
        return $this->feePayments()
            ->where('status', 'approved')
            ->whereDate('valid_until', '>=', now()->toDateString())
            ->orderByDesc('valid_until')
            ->first();
    }

    /** A payment still awaiting admin review, if the passenger has one. */
    public function pendingFeePayment(): ?FeePayment
    {
        return $this->feePayments()->where('status', 'pending')->latest()->first();
    }

    public function isPending(): bool  { return $this->approval_status === 'pending'; }
    public function isApproved(): bool { return $this->approval_status === 'approved'; }
    public function isRejected(): bool { return $this->approval_status === 'rejected'; }

    public function hasRequestedCancellation(): bool { return $this->cancellation_status === 'requested'; }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * True once the transport application has been approved AND the current
     * month's fee has been paid and approved.
     */
    public function hasActivePass(): bool
    {
        return $this->isApproved() && $this->activeFeePayment() !== null;
    }
}
