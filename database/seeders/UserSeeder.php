<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Admin User',
                'email'    => 'admin@tm.com',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ],
            [
                'name'     => 'Incharge User',
                'email'    => 'incharge@tm.com',
                'password' => Hash::make('in123'),
                'role'     => 'incharge',
            ],
            [
                'name'     => 'Passenger User',
                'email'    => 'pass@tm.com',
                'password' => Hash::make('pass123'),
                'role'     => 'passenger',
            ],
            [
                'name'     => 'Driver User',
                'email'    => 'driver@tm.com',
                'password' => Hash::make('drv123'),
                'role'     => 'driver',
            ],
            [
                'name'     => 'Scanner User',
                'email'    => 'scan@tm.com',
                'password' => Hash::make('scan123'),
                'role'     => 'scanner',
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],  // match on email
                $userData                          // fill these if creating
            );
        }
    }
}
