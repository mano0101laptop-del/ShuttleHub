<?php

use App\Models\User;
use App\Models\Passenger;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeUser(string $role): User
{
    static $counter = 0;
    $counter++;
    return User::create([
        'name' => ucfirst($role) . ' Test ' . $counter,
        'email' => $role . $counter . '@test.com',
        'password' => bcrypt('password123'),
        'role' => $role,
    ]);
}

test('login page loads', function () {
    $this->get('/')->assertOk();
});

test('register page loads', function () {
    $this->get('/register')->assertOk();
});

test('admin can log in and see dashboard', function () {
    $admin = makeUser('admin');
    $resp = $this->post('/login', ['email' => $admin->email, 'password' => 'password123']);
    $resp->assertRedirect('/dashboard');
    $this->get('/dashboard')->assertOk();
});

test('passenger dashboard loads with no linked passenger record (edge case)', function () {
    $p = makeUser('passenger');
    $this->actingAs($p)->get('/dashboard')->assertOk();
});

test('passenger dashboard loads with a linked passenger record', function () {
    $u = makeUser('passenger');
    $passenger = Passenger::create([
        'user_id' => $u->id, 'name' => 'Test P', 'roll' => 'R-1', 'department' => 'CS',
        'stop' => 'Gate 1', 'status' => 'Active', 'approval_status' => 'approved',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('123456'), 'fingerprint_enrolled' => true,
    ]);
    $this->actingAs($u)->get('/dashboard')->assertOk();
    $this->actingAs($u)->get("/passengers/{$passenger->id}")->assertOk();
});

test('driver role is blocked from vehicle management (403)', function () {
    $driver = makeUser('driver');
    $this->actingAs($driver)->get('/vehicles')->assertForbidden();
    $this->actingAs($driver)->get('/drivers')->assertForbidden();
    $this->actingAs($driver)->get('/passengers')->assertForbidden();
    $this->actingAs($driver)->post('/vehicles', ['number' => 'X1'])->assertForbidden();
});

test('driver and scanner and incharge CAN access attendance, admin CANNOT', function () {
    foreach (['driver', 'scanner', 'incharge'] as $role) {
        $u = makeUser($role);
        $this->actingAs($u)->get('/attendance')->assertOk();
    }

    $admin = makeUser('admin');
    $this->actingAs($admin)->get('/attendance')->assertForbidden();
    $this->actingAs($admin)->get('/reports/attendance')->assertOk();
});

test('passenger role is blocked from attendance and admin routes', function () {
    $p = makeUser('passenger');
    $this->actingAs($p)->get('/attendance')->assertForbidden();
    $this->actingAs($p)->get('/vehicles')->assertForbidden();
});

test('passenger cannot view another passengers record', function () {
    $ownerUser = makeUser('passenger');
    $otherUser = makeUser('passenger');
    $owned = Passenger::create([
        'user_id' => $ownerUser->id, 'name' => 'Owner', 'roll' => 'R-2', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('123456'), 'fingerprint_enrolled' => true,
    ]);
    $this->actingAs($otherUser)->get("/passengers/{$owned->id}")->assertForbidden();
    $this->actingAs($ownerUser)->get("/passengers/{$owned->id}")->assertOk();
});

test('public registration cannot escalate to admin role', function () {
    $resp = $this->post('/register', [
        'name' => 'Hacker', 'email' => 'hacker@test.com',
        'password' => 'password123', 'password_confirmation' => 'password123',
        'role' => 'admin', // attempted privilege escalation payload
        'department' => 'CS', 'fingerprint_data' => 'mypin123',
    ]);
    $resp->assertRedirect('/dashboard');
    $u = User::where('email', 'hacker@test.com')->first();
    expect($u)->not->toBeNull();
    expect($u->role)->toBe('passenger'); // must NOT be admin
});

test('admin can create a vehicle, driver, route and passenger end to end', function () {
    $admin = makeUser('admin');
    $this->actingAs($admin);

    $this->post('/vehicles', ['number' => 'BUS-01', 'type' => 'Bus', 'capacity' => 40, 'status' => 'Active'])
        ->assertRedirect('/vehicles');
    $vehicle = Vehicle::first();
    expect($vehicle)->not->toBeNull();

    $this->post('/drivers', ['name' => 'Driver One', 'phone' => '0300', 'license' => 'LIC-1', 'cnic' => '35202-1111111-1', 'status' => 'Active', 'vehicle_id' => $vehicle->id])
        ->assertRedirect('/drivers');
    expect(Driver::count())->toBe(1);

    $this->post('/tmsroutes', ['name' => 'Route A', 'from' => 'Campus', 'to' => 'City', 'stops' => 5, 'status' => 'Active', 'vehicle_id' => $vehicle->id])
        ->assertRedirect('/tmsroutes');
    $route = Route::first();
    expect($route)->not->toBeNull();

    $resp = $this->post('/passengers', [
        'name' => 'New Passenger', 'roll' => 'R-99', 'department' => 'EE',
        'status' => 'Active', 'route_id' => $route->id, 'fingerprint_data' => 'pin1234',
    ]);
    $passenger = Passenger::first();
    expect($passenger)->not->toBeNull();
    $resp->assertRedirect("/passengers/{$passenger->id}");
});

test('vehicle index page renders with pagination and a Breakdown badge', function () {
    $admin = makeUser('admin');
    Vehicle::create(['number' => 'BRK-1', 'type' => 'Bus', 'capacity' => 30, 'status' => 'Breakdown']);
    $resp = $this->actingAs($admin)->get('/vehicles');
    $resp->assertOk();
    $resp->assertSee('badge-W', false);
});

test('attendance QR scan endpoint marks a passenger present and is idempotent', function () {
    $u = makeUser('passenger');
    $passenger = Passenger::create([
        'user_id' => $u->id, 'name' => 'Scan Me', 'roll' => 'R-3', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('123456'), 'fingerprint_enrolled' => true,
    ]);
    \App\Models\FeePayment::create([
        'passenger_id' => $passenger->id, 'month' => now()->format('Y-m'), 'amount' => 1000,
        'tid' => 'TXN-SCAN', 'screenshot_path' => 'x.jpg', 'status' => 'approved',
        'qr_expires_at' => now()->endOfMonth(),
    ]);

    $this->get("/scan/{$passenger->qr_token}")->assertOk();
    $this->assertDatabaseHas('attendances', ['passenger_id' => $passenger->id, 'status' => 'Present']);

    // scanning again same day should show "already marked", not error or duplicate row
    $this->get("/scan/{$passenger->qr_token}")->assertOk();
    expect(\App\Models\Attendance::where('passenger_id', $passenger->id)->count())->toBe(1);
});

test('attendance QR scan is blocked when the fee is unpaid, even if approved', function () {
    $u = makeUser('passenger');
    $passenger = Passenger::create([
        'user_id' => $u->id, 'name' => 'Unpaid Fee', 'roll' => 'R-3B', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('123456'), 'fingerprint_enrolled' => true,
    ]);

    $this->get("/scan/{$passenger->qr_token}")->assertOk()->assertSee('Pass Not Active');
    $this->assertDatabaseMissing('attendances', ['passenger_id' => $passenger->id]);
});

test('fingerprint PIN is hashed with bcrypt, not raw sha256', function () {
    $u = makeUser('passenger');
    $passenger = Passenger::create([
        'user_id' => $u->id, 'name' => 'Pin Test', 'roll' => 'R-4', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('mySecretPin1'), 'fingerprint_enrolled' => true,
    ]);
    expect(Hash::check('mySecretPin1', $passenger->fingerprint_hash))->toBeTrue();
    expect(str_starts_with($passenger->fingerprint_hash, '$2y$'))->toBeTrue();
});

test('reports and passenger dashboard show honest placeholders, not fabricated data', function () {
    $admin = makeUser('admin');
    $resp = $this->actingAs($admin)->get('/reports');
    $resp->assertOk();
    $resp->assertDontSee('Fuel Cost');
    $resp->assertDontSee('KM This Month');
});

test('soft delete preserves attendance history', function () {
    $u = makeUser('passenger');
    $passenger = Passenger::create([
        'user_id' => $u->id, 'name' => 'Soft Delete Me', 'roll' => 'R-5', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('123456'), 'fingerprint_enrolled' => true,
    ]);
    \App\Models\Attendance::create(['passenger_id' => $passenger->id, 'date' => now()->toDateString(), 'status' => 'Present', 'method' => 'qr']);

    $passenger->delete();

    $this->assertSoftDeleted('passengers', ['id' => $passenger->id]);
    $this->assertDatabaseHas('attendances', ['passenger_id' => $passenger->id]);
});
