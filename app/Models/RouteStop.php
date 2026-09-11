<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStop extends Model
{
    protected $fillable = ['route_id', 'name', 'sequence', 'eta'];

    protected $casts = [
        'sequence' => 'integer',
    ];

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }
}
