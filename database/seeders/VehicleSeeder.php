<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            ['number' => 'GWR-2201', 'type' => 'Bus',      'capacity' => 52, 'status' => 'Active'],
            ['number' => 'GWR-2202', 'type' => 'Bus',      'capacity' => 52, 'status' => 'Active'],
            ['number' => 'GWR-2203', 'type' => 'Coaster',  'capacity' => 28, 'status' => 'Active'],
            ['number' => 'GWR-2204', 'type' => 'Coaster',  'capacity' => 28, 'status' => 'Active'],
            ['number' => 'GWR-2205', 'type' => 'Van',      'capacity' => 14, 'status' => 'Active'],
            ['number' => 'GWR-2206', 'type' => 'Van',      'capacity' => 14, 'status' => 'Breakdown'],
            ['number' => 'GWR-2207', 'type' => 'Bus',      'capacity' => 52, 'status' => 'Inactive'],
        ];

        foreach ($vehicles as $vehicle) {
            Vehicle::firstOrCreate(['number' => $vehicle['number']], $vehicle);
        }
    }
}
