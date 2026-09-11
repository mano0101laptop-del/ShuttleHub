<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isAdmin(): bool     { return $this->role === 'admin'; }
    public function isIncharge(): bool  { return $this->role === 'incharge'; }
    public function isPassenger(): bool { return $this->role === 'passenger'; }
    public function isDriver(): bool    { return $this->role === 'driver'; }

    /** Linked Passenger record (only for passenger-role users) */
    public function passenger()
    {
        return $this->hasOne(Passenger::class);
    }

    /** Linked Driver profile (only for driver-role users) */
    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    /** Messages in this user's driver-portal thread (only meaningful for driver-role users) */
    public function driverMessages()
    {
        return $this->hasMany(Message::class, 'driver_user_id');
    }
}
