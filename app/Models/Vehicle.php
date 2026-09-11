<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use SoftDeletes;

    protected $fillable = ['number', 'type', 'capacity', 'status'];

    public function driver(): HasOne
    {
        return $this->hasOne(Driver::class);
    }

    public function route(): HasOne
    {
        return $this->hasOne(Route::class);
    }

    public function dailyAssignments(): HasMany
    {
        return $this->hasMany(DailyAssignment::class);
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }
}
