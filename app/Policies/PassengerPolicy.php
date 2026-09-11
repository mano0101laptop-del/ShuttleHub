<?php

namespace App\Policies;

use App\Models\Passenger;
use App\Models\User;

class PassengerPolicy
{
    /**
     * Staff (admin/incharge) can view anyone. A passenger-role user can only
     * view their own linked passenger record.
     */
    public function view(User $user, Passenger $passenger): bool
    {
        return in_array($user->role, ['admin', 'incharge'], true)
            || $user->id === $passenger->user_id;
    }

    /** Only staff manage (create/edit/delete) passenger records. */
    public function manage(User $user): bool
    {
        return in_array($user->role, ['admin', 'incharge'], true);
    }

    /** Only staff approve or reject applications. */
    public function approve(User $user): bool
    {
        return in_array($user->role, ['admin', 'incharge'], true);
    }
}
