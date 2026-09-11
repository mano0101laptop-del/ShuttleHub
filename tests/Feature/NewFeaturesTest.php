<?php

use App\Models\User;
use App\Models\Passenger;
use App\Models\Driver;
use App\Models\FeePayment;
use App\Models\Message;
use App\Models\Announcement;
use App\Models\Complaint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function makeRoleUser(string $role, string $prefix): User
{
    static $counter = 0;
    $counter++;
    return User::create([
        'name' => ucfirst($role) . ' ' . $counter,
        'email' => $prefix . $counter . '@t.com',
        'password' => bcrypt('password123'),
        'role' => $role,
    ]);
}

test('creating a driver auto-provisions a portal login', function () {
    $admin = makeRoleUser('admin', 'da');
    $this->actingAs($admin)->post('/drivers', [
        'name' => 'New Driver', 'phone' => '0300-1111111', 'license' => 'LIC-NEW',
        'cnic' => '35202-1234567-1', 'status' => 'Active',
    ])->assertRedirect('/drivers');

    $driver = Driver::where('license', 'LIC-NEW')->first();
    expect($driver)->not->toBeNull();
    expect($driver->user)->not->toBeNull();
    expect($driver->user->role)->toBe('driver');
});

test('driver can only see and reply in their own message thread', function () {
    $admin = makeRoleUser('admin', 'ma');
    $driverUser1 = makeRoleUser('driver', 'md1');
    $driverUser2 = makeRoleUser('driver', 'md2');

    $this->actingAs($admin)->post("/messages/{$driverUser1->id}", ['body' => 'Hello driver 1'])->assertRedirect();

    // driver 1 can see their own thread
    $this->actingAs($driverUser1)->get("/messages/{$driverUser1->id}")->assertOk()->assertSee('Hello driver 1');

    // driver 2 cannot see driver 1's thread
    $this->actingAs($driverUser2)->get("/messages/{$driverUser1->id}")->assertForbidden();

    expect(Message::where('driver_user_id', $driverUser1->id)->count())->toBe(1);
});

test('admin can publish an announcement and it appears on the passenger dashboard', function () {
    $admin = makeRoleUser('admin', 'aa');
    $this->actingAs($admin)->post('/announcements', [
        'title' => 'Route Change', 'body' => 'Route A moved to Gate 2.', 'audience' => 'passengers',
    ])->assertRedirect();

    expect(Announcement::count())->toBe(1);

    $pu = makeRoleUser('passenger', 'ap');
    $passenger = Passenger::create([
        'user_id' => $pu->id, 'name' => 'Ann P', 'roll' => 'ANN-1', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('123456'), 'fingerprint_enrolled' => true,
    ]);

    $this->actingAs($pu)->get('/dashboard')->assertOk()->assertSee('Route Change');
});

test('passenger can submit a complaint against a driver and admin can respond', function () {
    $admin = makeRoleUser('admin', 'ca');
    $pu = makeRoleUser('passenger', 'cp');
    $passenger = Passenger::create([
        'user_id' => $pu->id, 'name' => 'Complainer', 'roll' => 'CMP-1', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('123456'), 'fingerprint_enrolled' => true,
    ]);
    $driver = Driver::create(['name' => 'Bad Driver', 'phone' => '0300', 'license' => 'L-CMP', 'status' => 'Active']);

    $this->actingAs($pu)->post('/complaints', [
        'type' => 'complaint', 'against_type' => 'driver', 'against_driver_id' => $driver->id,
        'subject' => 'Rude behavior', 'message' => 'The driver was rude at the stop.',
    ])->assertRedirect();

    $complaint = Complaint::first();
    expect($complaint)->not->toBeNull();
    expect($complaint->against_driver_id)->toBe($driver->id);

    $this->actingAs($admin)->post("/complaints/{$complaint->id}/respond", [
        'admin_response' => 'We spoke to the driver.', 'status' => 'resolved',
    ])->assertRedirect();

    expect($complaint->fresh()->status)->toBe('resolved');
});

test('passenger cannot download QR pass until approved and fee is active', function () {
    $pu = makeRoleUser('passenger', 'qp');
    $passenger = Passenger::create([
        'user_id' => $pu->id, 'name' => 'Locked QR', 'roll' => 'QR-1', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('123456'), 'fingerprint_enrolled' => true,
    ]);

    expect($passenger->qrIsActive())->toBeFalse();
    $this->actingAs($pu)->get("/passengers/{$passenger->id}/qr")->assertRedirect();

    FeePayment::create([
        'passenger_id' => $passenger->id, 'month' => now()->format('Y-m'), 'amount' => 1000,
        'tid' => 'TXN-QR', 'screenshot_path' => 'x.jpg', 'status' => 'approved',
        'qr_expires_at' => now()->endOfMonth(),
    ]);

    expect($passenger->fresh()->qrIsActive())->toBeTrue();
    $this->actingAs($pu)->get("/passengers/{$passenger->id}/qr")->assertOk();
});

test('passenger can request cancellation and admin can approve it', function () {
    $admin = makeRoleUser('admin', 'xa');
    $pu = makeRoleUser('passenger', 'xp');
    $passenger = Passenger::create([
        'user_id' => $pu->id, 'name' => 'Cancel Me', 'roll' => 'CAN-1', 'department' => 'CS',
        'status' => 'Active', 'approval_status' => 'approved',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('123456'), 'fingerprint_enrolled' => true,
    ]);

    $this->actingAs($pu)->post("/passengers/{$passenger->id}/cancellation", [
        'cancellation_reason' => 'Moving to a new city.',
    ])->assertRedirect();

    expect($passenger->fresh()->cancellation_status)->toBe('requested');

    $this->actingAs($admin)->post("/passengers/{$passenger->id}/cancellation/approve")->assertRedirect();

    $passenger->refresh();
    expect($passenger->cancellation_status)->toBe('approved');
    expect($passenger->status)->toBe('Inactive');
});

test('admin can view a full driver profile and a full incharge profile', function () {
    $admin = makeRoleUser('admin', 'dvp');
    $vehicle = \App\Models\Vehicle::create(['number' => 'DVP-1', 'type' => 'Bus', 'capacity' => 30, 'status' => 'Active']);
    $driver = Driver::create([
        'name' => 'Full Profile Driver', 'phone' => '0301', 'license' => 'L-DVP', 'cnic' => '12345-6789012-3',
        'status' => 'Active', 'vehicle_id' => $vehicle->id,
    ]);
    $incharge = makeRoleUser('incharge', 'dvpi');

    $this->actingAs($admin)->get("/drivers/{$driver->id}")->assertOk()->assertSee('Full Profile Driver');
    $this->actingAs($admin)->get("/staff/{$incharge->id}")->assertOk()->assertSee($incharge->name);
});

test('admin sees the attendance report but not the attendance marking screen', function () {
    $admin = makeRoleUser('admin', 'rpt');

    foreach (['daily', 'weekly', 'monthly', 'yearly'] as $period) {
        $this->actingAs($admin)->get("/reports/attendance?period={$period}")->assertOk();
    }

    $this->actingAs($admin)->get('/attendance')->assertForbidden();
});

test('passenger create form offers route stops for dynamic selection', function () {
    $incharge = makeRoleUser('incharge', 'stp');
    $vehicle = \App\Models\Vehicle::create(['number' => 'STP-1', 'type' => 'Bus', 'capacity' => 30, 'status' => 'Active']);
    $route = \App\Models\Route::create(['name' => 'Stop Route', 'from' => 'A', 'to' => 'B', 'stops' => 1, 'status' => 'Active', 'vehicle_id' => $vehicle->id]);
    \App\Models\RouteStop::create(['route_id' => $route->id, 'name' => 'Main Gate', 'sequence' => 1, 'eta' => '8:15 AM']);

    $resp = $this->actingAs($incharge)->get('/passengers/create');
    $resp->assertOk();
    $resp->assertSee('Main Gate');
});
