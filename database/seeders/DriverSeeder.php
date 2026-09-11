<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DriverSeeder extends Seeder
{
    public function run(): void
    {
        $drivers = [
            ['name' => 'Muhammad Asif',    'phone' => '0300-1234567', 'license' => 'LHR-DR-10001', 'cnic' => '35202-1000001-1', 'experience' => '8 years',  'status' => 'Active',   'vehicle_number' => 'GWR-2201'],
            ['name' => 'Tariq Mehmood',    'phone' => '0301-2345678', 'license' => 'LHR-DR-10002', 'cnic' => '35202-1000002-1', 'experience' => '5 years',  'status' => 'Active',   'vehicle_number' => 'GWR-2202'],
            ['name' => 'Shahid Iqbal',     'phone' => '0302-3456789', 'license' => 'LHR-DR-10003', 'cnic' => '35202-1000003-1', 'experience' => '10 years', 'status' => 'Active',   'vehicle_number' => 'GWR-2203'],
            ['name' => 'Waqas Ahmed',      'phone' => '0303-4567890', 'license' => 'LHR-DR-10004', 'cnic' => '35202-1000004-1', 'experience' => '3 years',  'status' => 'Active',   'vehicle_number' => 'GWR-2204'],
            ['name' => 'Imran Sheikh',     'phone' => '0304-5678901', 'license' => 'LHR-DR-10005', 'cnic' => '35202-1000005-1', 'experience' => '6 years',  'status' => 'Active',   'vehicle_number' => 'GWR-2205'],
            ['name' => 'Bilal Hussain',    'phone' => '0305-6789012', 'license' => 'LHR-DR-10006', 'cnic' => '35202-1000006-1', 'experience' => '2 years',  'status' => 'Inactive', 'vehicle_number' => null],
        ];

        foreach ($drivers as $driver) {
            $vehicleId = $driver['vehicle_number']
                ? Vehicle::where('number', $driver['vehicle_number'])->value('id')
                : null;

            $existing = Driver::where('license', $driver['license'])->first();
            if ($existing) {
                continue;
            }

            // Every seeded driver gets a portal login too, same as one created
            // through the Add Driver form — keeps demo data consistent with
            // real usage (driver messaging, driver dashboard, etc).
            // The first seeded driver reuses the documented demo login
            // (driver@tm.com, created by UserSeeder) so that account has a
            // real profile to show off; the rest get their own auto-generated
            // logins, same as drivers added through the Add Driver form.
            $loginEmail = $driver['license'] === 'LHR-DR-10001'
                ? 'driver@tm.com'
                : 'driver.' . strtolower(str_replace(['LHR-DR-', ' '], '', $driver['license'])) . '@drivers.shuttlehub.local';

            $user = User::firstOrCreate(
                ['email' => $loginEmail],
                [
                    'name'     => $driver['name'],
                    'password' => Hash::make('driver12345'), // demo-only default password
                    'role'     => 'driver',
                ]
            );

            Driver::create([
                'user_id'    => $user->id,
                'name'       => $driver['name'],
                'phone'      => $driver['phone'],
                'license'    => $driver['license'],
                'cnic'       => $driver['cnic'],
                'experience' => $driver['experience'],
                'status'     => $driver['status'],
                'vehicle_id' => $vehicleId,
            ]);
        }
    }
}
