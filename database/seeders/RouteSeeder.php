<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        $routes = [
            ['name' => 'Route A - Gulberg',        'from' => 'Gulberg',        'to' => 'Campus', 'stops' => 4, 'status' => 'Active', 'vehicle_number' => 'GWR-2201'],
            ['name' => 'Route B - Model Town',     'from' => 'Model Town',     'to' => 'Campus', 'stops' => 5, 'status' => 'Active', 'vehicle_number' => 'GWR-2202'],
            ['name' => 'Route C - Wapda Town',      'from' => 'Wapda Town',     'to' => 'Campus', 'stops' => 3, 'status' => 'Active', 'vehicle_number' => 'GWR-2203'],
            ['name' => 'Route D - Johar Town',      'from' => 'Johar Town',     'to' => 'Campus', 'stops' => 6, 'status' => 'Active', 'vehicle_number' => 'GWR-2204'],
            ['name' => 'Route E - DHA Phase 5',     'from' => 'DHA Phase 5',    'to' => 'Campus', 'stops' => 4, 'status' => 'Active', 'vehicle_number' => 'GWR-2205'],
            ['name' => 'Route F - Township',        'from' => 'Township',       'to' => 'Campus', 'stops' => 3, 'status' => 'Inactive', 'vehicle_number' => null],
        ];

        foreach ($routes as $route) {
            $vehicleId = $route['vehicle_number']
                ? Vehicle::where('number', $route['vehicle_number'])->value('id')
                : null;

            $r = Route::firstOrCreate(
                ['name' => $route['name']],
                [
                    'from'       => $route['from'],
                    'to'         => $route['to'],
                    'stops'      => $route['stops'],
                    'status'     => $route['status'],
                    'vehicle_id' => $vehicleId,
                ]
            );

            // Seed a couple of demo stops so the multi-stop feature has
            // something to show out of the box, if this route has none yet.
            if ($r->routeStops()->count() === 0) {
                $r->routeStops()->createMany([
                    ['name' => $route['from'] . ' Pickup Point', 'sequence' => 1],
                    ['name' => 'Mid Route Stop',                 'sequence' => 2],
                    ['name' => $route['to'],                     'sequence' => 3],
                ]);
                $r->syncStopsCount();
            }
        }
    }
}
