<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Passenger extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name', 'roll', 'department', 'stop', 'status',
        'approval_status',
        'cancellation_status', 'cancellation_reason', 'cancellation_requested_at',
        'route_id', 'route_stop_id', 'driver_id', 'vehicle_id', 'pickup_time', 'dropoff_time',
        'qr_token', 'fingerprint_hash', 'fingerprint_enrolled',
    ];

    protected $casts = [
        'fingerprint_enrolled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class);
    }

    public function assignedDriver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }

    public function assignedVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
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
            ->whereDate('qr_expires_at', '>=', now()->toDateString())
            ->orderByDesc('qr_expires_at')
            ->first();
    }

    /** A payment still awaiting admin review, if the passenger has one. */
    public function pendingFeePayment(): ?FeePayment
    {
        return $this->feePayments()->where('status', 'pending')->latest()->first();
    }

    public static function generateQrToken(): string
    {
        do {
            $token = strtoupper(Str::random(12));
        } while (self::where('qr_token', $token)->exists());

        return $token;
    }

    public function qrUrl(): string
    {
        return route('attendance.scan', ['token' => $this->qr_token]);
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
     * The QR pass (attendance + fee proof) is only "live" once the transport
     * application has been approved AND the current month's fee has been
     * paid and approved. There is no separate fee QR — this single token
     * covers both.
     */
    public function qrIsActive(): bool
    {
        return $this->isApproved() && $this->activeFeePayment() !== null;
    }
}
