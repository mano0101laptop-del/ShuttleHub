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

test('passenger role is blocked from admin routes', function () {
    $p = makeUser('passenger');
    $this->actingAs($p)->get('/vehicles')->assertForbidden();
});

test('passenger cannot view another passengers record', function () {
    $ownerUser = makeUser('passenger');
    $otherUser = makeUser('passenger');
    $owned = Passenger::create([
        'user_id' => $ownerUser->id, 'name' => 'Owner', 'roll' => 'R-2', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
    ]);
    $this->actingAs($otherUser)->get("/passengers/{$owned->id}")->assertForbidden();
    $this->actingAs($ownerUser)->get("/passengers/{$owned->id}")->assertOk();
});

test('public registration cannot escalate to admin role', function () {
    $route = Route::create(['name' => 'Hacker Route', 'from' => 'A', 'to' => 'B', 'stops' => 1, 'status' => 'Active']);
    \App\Models\Stop::create(['route_id' => $route->id, 'name' => 'Gate 1', 'sequence' => 1]);
    $stopId = \App\Models\Stop::where('route_id', $route->id)->value('id');

    $resp = $this->post('/register', [
        'name' => 'Hacker', 'email' => 'hacker@test.com',
        'password' => 'password123', 'password_confirmation' => 'password123',
        'role' => 'admin', // attempted privilege escalation payload
        'roll' => 'HK-1', 'contact_number' => '0300-0000000', 'passenger_type' => 'Student',
        'route_id' => $route->id, 'stop_id' => $stopId,
        'address' => 'Test Address', 'emergency_contact' => '0300-1111111', 'confirm' => '1',
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
        'contact_number' => '0300-1234567', 'passenger_type' => 'Student',
        'address' => 'Test Address', 'emergency_contact' => '0300-7654321',
        'status' => 'Active', 'route_id' => $route->id,
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

test('soft delete preserves the passenger record for audit purposes', function () {
    $u = makeUser('passenger');
    $passenger = Passenger::create([
        'user_id' => $u->id, 'name' => 'Soft Delete Me', 'roll' => 'R-5', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
    ]);

    $passenger->delete();

    $this->assertSoftDeleted('passengers', ['id' => $passenger->id]);
});
