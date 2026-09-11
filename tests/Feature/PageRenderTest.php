<?php

use App\Models\User;
use App\Models\Passenger;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('every admin page renders without error', function () {
    $admin = User::create(['name'=>'A','email'=>'a1@t.com','password'=>bcrypt('x'),'role'=>'admin']);
    $vehicle = Vehicle::create(['number'=>'V1','type'=>'Bus','capacity'=>30,'status'=>'Active']);
    $driver = Driver::create(['name'=>'D1','phone'=>'123','license'=>'L1','status'=>'Active','vehicle_id'=>$vehicle->id]);
    $route = Route::create(['name'=>'R1','from'=>'A','to'=>'B','stops'=>3,'status'=>'Active','vehicle_id'=>$vehicle->id]);
    $pu = User::create(['name'=>'P','email'=>'p1@t.com','password'=>bcrypt('x'),'role'=>'passenger']);
    $passenger = Passenger::create([
        'user_id'=>$pu->id,'name'=>'P1','roll'=>'R-1','department'=>'CS','status'=>'Active',
        'approval_status'=>'approved','route_id'=>$route->id,'qr_token'=>Passenger::generateQrToken(),
        'fingerprint_hash'=>Hash::make('123456'),'fingerprint_enrolled'=>true,
    ]);
    \App\Models\Attendance::create(['passenger_id'=>$passenger->id,'date'=>now()->toDateString(),'status'=>'Present','method'=>'qr']);
    \App\Models\FeePayment::create([
        'passenger_id' => $passenger->id, 'month' => now()->format('Y-m'), 'amount' => 1000,
        'tid' => 'TXN-PAGE', 'screenshot_path' => 'x.jpg', 'status' => 'approved',
        'qr_expires_at' => now()->endOfMonth(),
    ]);

    $this->actingAs($admin);

    $pages = [
        '/dashboard', '/vehicles', '/vehicles/create', "/vehicles/{$vehicle->id}/edit",
        '/drivers', '/drivers/create', "/drivers/{$driver->id}/edit",
        '/tmsroutes', '/tmsroutes/create', "/tmsroutes/{$route->id}/edit",
        '/passengers', '/passengers/create', "/passengers/{$passenger->id}", "/passengers/{$passenger->id}/edit",
        '/reports/attendance', '/fee-payments',
        "/passengers/{$passenger->id}/qr",
    ];

    foreach ($pages as $url) {
        $resp = $this->get($url);
        expect($resp->getStatusCode())->toBe(200, "Page $url returned {$resp->getStatusCode()}");
    }
});

test('QR scan result page renders for both fresh and already-marked states', function () {
    $pu = User::create(['name'=>'P','email'=>'p2@t.com','password'=>bcrypt('x'),'role'=>'passenger']);
    $passenger = Passenger::create([
        'user_id'=>$pu->id,'name'=>'P2','roll'=>'R-2','department'=>'CS','status'=>'Active',
        'approval_status'=>'approved','qr_token'=>Passenger::generateQrToken(),
        'fingerprint_hash'=>Hash::make('123456'),'fingerprint_enrolled'=>true,
    ]);
    \App\Models\FeePayment::create([
        'passenger_id' => $passenger->id, 'month' => now()->format('Y-m'), 'amount' => 1000,
        'tid' => 'TXN-SCAN2', 'screenshot_path' => 'x.jpg', 'status' => 'approved',
        'qr_expires_at' => now()->endOfMonth(),
    ]);
    $this->get("/scan/{$passenger->qr_token}")->assertOk();
    $this->get("/scan/{$passenger->qr_token}")->assertOk(); // already-marked branch
});

test('pending passenger applications appear on passengers index and can be approved', function () {
    $admin = User::create(['name'=>'A','email'=>'a2@t.com','password'=>bcrypt('x'),'role'=>'admin']);
    $pu = User::create(['name'=>'P','email'=>'p3@t.com','password'=>bcrypt('x'),'role'=>'passenger']);
    $passenger = Passenger::create([
        'user_id'=>$pu->id,'name'=>'Pending Guy','roll'=>'R-3','department'=>'CS','status'=>'Inactive',
        'approval_status'=>'pending','qr_token'=>Passenger::generateQrToken(),
        'fingerprint_hash'=>Hash::make('123456'),'fingerprint_enrolled'=>true,
    ]);

    $resp = $this->actingAs($admin)->get('/passengers');
    $resp->assertOk();
    $resp->assertSee('Pending Guy');

    $this->actingAs($admin)->post("/passengers/{$passenger->id}/approve")->assertRedirect();
    $passenger->refresh();
    expect($passenger->approval_status)->toBe('approved');
    expect($passenger->status)->toBe('Active');
});

test('manual attendance can be marked via the attendance form', function () {
    $incharge = User::create(['name'=>'I','email'=>'i3@t.com','password'=>bcrypt('x'),'role'=>'incharge']);
    $pu = User::create(['name'=>'P','email'=>'p4@t.com','password'=>bcrypt('x'),'role'=>'passenger']);
    $passenger = Passenger::create([
        'user_id'=>$pu->id,'name'=>'P4','roll'=>'R-4','department'=>'CS','status'=>'Active',
        'approval_status'=>'approved','qr_token'=>Passenger::generateQrToken(),
        'fingerprint_hash'=>Hash::make('123456'),'fingerprint_enrolled'=>true,
    ]);
    \App\Models\FeePayment::create([
        'passenger_id' => $passenger->id, 'month' => now()->format('Y-m'), 'amount' => 1000,
        'tid' => 'TXN-MANUAL', 'screenshot_path' => 'x.jpg', 'status' => 'approved',
        'qr_expires_at' => now()->endOfMonth(),
    ]);

    $resp = $this->actingAs($incharge)->post('/attendance', [
        'passenger_id' => $passenger->id,
        'date' => now()->toDateString(),
        'status' => 'Present',
        'method' => 'manual',
    ]);
    $resp->assertRedirect('/attendance');
    $this->assertDatabaseHas('attendances', ['passenger_id' => $passenger->id, 'method' => 'manual']);
});

test('fingerprint verify AJAX endpoint works end to end', function () {
    $incharge = User::create(['name'=>'I','email'=>'i4@t.com','password'=>bcrypt('x'),'role'=>'incharge']);
    $pu = User::create(['name'=>'P','email'=>'p5@t.com','password'=>bcrypt('x'),'role'=>'passenger']);
    $passenger = Passenger::create([
        'user_id'=>$pu->id,'name'=>'P5','roll'=>'R-5','department'=>'CS','status'=>'Active',
        'approval_status'=>'approved','qr_token'=>Passenger::generateQrToken(),
        'fingerprint_hash'=>Hash::make('mypin99'),'fingerprint_enrolled'=>true,
    ]);
    \App\Models\FeePayment::create([
        'passenger_id' => $passenger->id, 'month' => now()->format('Y-m'), 'amount' => 1000,
        'tid' => 'TXN-FP', 'screenshot_path' => 'x.jpg', 'status' => 'approved',
        'qr_expires_at' => now()->endOfMonth(),
    ]);

    $resp = $this->actingAs($incharge)->postJson('/attendance/fingerprint-verify', [
        'passenger_id' => $passenger->id,
        'fingerprint_data' => 'mypin99',
    ]);
    $resp->assertOk();
    $resp->assertJson(['success' => true]);

    $wrong = $this->actingAs($incharge)->postJson('/attendance/fingerprint-verify', [
        'passenger_id' => $passenger->id,
        'fingerprint_data' => 'wrongpin',
    ]);
    $wrong->assertStatus(422);
});
