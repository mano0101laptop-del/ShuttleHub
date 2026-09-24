<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeSetting extends Model
{
    protected $fillable = ['monthly_fee', 'payment_instructions', 'jazzcash_number', 'easypaisa_number', 'updated_by'];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
    ];

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * There is only ever one row. Fetch it (creating it with a 0 fee the
     * very first time the app runs) instead of scattering
     * firstOrCreate() calls across controllers.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['monthly_fee' => 0]);
    }

    public static function amount(): float
    {
        return (float) static::current()->monthly_fee;
    }
}
