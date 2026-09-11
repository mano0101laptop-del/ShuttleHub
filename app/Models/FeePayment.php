<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FeePayment extends Model
{
    protected $fillable = [
        'passenger_id',
        'month',
        'amount',
        'tid',
        'screenshot_path',
        'status',
        'rejection_reason',
        'qr_token',
        'qr_expires_at',
        'approved_by',
        'approved_at',
        'payment_method',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'currency',
    ];

    protected $casts = [
        'qr_expires_at' => 'date',
        'approved_at'   => 'datetime',
        'amount'        => 'decimal:2',
    ];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    /** Approved AND the QR pass hasn't rolled past the paid month yet. */
    public function isActive(): bool
    {
        return $this->isApproved()
            && $this->qr_expires_at !== null
            && $this->qr_expires_at->gte(now()->startOfDay());
    }

    public function isExpired(): bool
    {
        return $this->isApproved()
            && $this->qr_expires_at !== null
            && $this->qr_expires_at->lt(now()->startOfDay());
    }

    public function monthLabel(): string
    {
        return Carbon::createFromFormat('Y-m', $this->month)->format('F Y');
    }

}
