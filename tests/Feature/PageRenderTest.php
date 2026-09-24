<?php

use App\Models\User;
use App\Models\Passenger;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('every admin page renders without error', function () {
    $admin = User::create(['name'=>'A','email'=>'a1@t.com','password'=>bcrypt('x'),'role'=>'admin']);
    $vehicle = Vehicle::create(['number'=>'V1','type'=>'Bus','capacity'=>30,'status'=>'Active']);
    $driver = Driver::create(['name'=>'D1','phone'=>'123','license'=>'L1','status'=>'Active','vehicle_id'=>$vehicle->id]);
    $route = Route::create(['name'=>'R1','from'=>'A','to'=>'B','stops'=>3,'status'=>'Active','vehicle_id'=>$vehicle->id]);
    $pu = User::create(['name'=>'P','email'=>'p1@t.com','password'=>bcrypt('x'),'role'=>'passenger']);
    $passenger = Passenger::create([
        'user_id'=>$pu->id,'name'=>'P1','roll'=>'R-1','department'=>'CS','status'=>'Active',
        'approval_status'=>'approved','route_id'=>$route->id,
    ]);
    \App\Models\FeePayment::create([
        'passenger_id' => $passenger->id, 'month' => now()->format('Y-m'), 'amount' => 1000,
        'tid' => 'TXN-PAGE', 'screenshot_path' => 'x.jpg', 'status' => 'approved',
        'valid_until' => now()->endOfMonth(),
    ]);

    $this->actingAs($admin);

    $pages = [
        '/dashboard', '/vehicles', '/vehicles/create', "/vehicles/{$vehicle->id}/edit",
        '/drivers', '/drivers/create', "/drivers/{$driver->id}/edit",
        '/tmsroutes', '/tmsroutes/create', "/tmsroutes/{$route->id}/edit",
        '/passengers', '/passengers/create', "/passengers/{$passenger->id}", "/passengers/{$passenger->id}/edit",
        '/fee-payments', '/schedule', '/schedule/create', '/announcements', '/complaints', '/complaints/create',
    ];

    foreach ($pages as $url) {
        $resp = $this->get($url);
        expect($resp->getStatusCode())->toBe(200, "Page $url returned {$resp->getStatusCode()}");
    }
});

test('pending passenger applications appear on passengers index and can be approved', function () {
    $admin = User::create(['name'=>'A','email'=>'a2@t.com','password'=>bcrypt('x'),'role'=>'admin']);
    $pu = User::create(['name'=>'P','email'=>'p3@t.com','password'=>bcrypt('x'),'role'=>'passenger']);
    $passenger = Passenger::create([
        'user_id'=>$pu->id,'name'=>'Pending Guy','roll'=>'R-3','department'=>'CS','status'=>'Inactive',
        'approval_status'=>'pending',
    ]);

    $resp = $this->actingAs($admin)->get('/passengers');
    $resp->assertOk();
    $resp->assertSee('Pending Guy');

    $this->actingAs($admin)->post("/passengers/{$passenger->id}/approve")->assertRedirect();
    $passenger->refresh();
    expect($passenger->approval_status)->toBe('approved');
    expect($passenger->status)->toBe('Active');
});
