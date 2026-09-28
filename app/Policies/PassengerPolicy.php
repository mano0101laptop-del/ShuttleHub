<?php

namespace App\Policies;

use App\Models\Passenger;
use App\Models\User;

class PassengerPolicy
{
    
    public function view(User $user, Passenger $passenger): bool
    {
        return in_array($user->role, ['admin', 'incharge'], true)
            || $user->id === $passenger->user_id;
    }
public function manage(User $user): bool
    {
        return $user->role === 'admin';
    }

    
    public function approve(User $user): bool
    {
        return $user->role === 'admin';
    }
}
