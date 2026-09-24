<?php

use App\Models\Passenger;
use App\Models\Route;
use App\Models\Stop;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('passenger can upload a profile photo used on dashboard and digital transport card', function () {
    Storage::fake('public');

    $vehicle = Vehicle::create([
        'number' => 'BUS-11',
        'type' => 'Bus',
        'capacity' => 30,
        'status' => 'Active',
    ]);

    $route = Route::create([
        'name' => 'Campus Express',
        'from' => 'City Centre',
        'to' => 'Main Campus',
        'stops' => 2,
        'status' => 'Active',
        'vehicle_id' => $vehicle->id,
    ]);

    $mainStop = Stop::create([
        'route_id' => $route->id,
        'name' => 'Main Gate',
        'sequence' => 1,
        'eta' => '08:10 AM',
    ]);

    Stop::create([
        'route_id' => $route->id,
        'name' => 'Library Stop',
        'sequence' => 2,
        'eta' => '08:20 AM',
    ]);

    $user = User::create([
        'name' => 'Card Passenger',
        'email' => 'card-passenger@example.test',
        'password' => bcrypt('password123'),
        'role' => 'passenger',
    ]);

    $passenger = Passenger::create([
        'user_id' => $user->id,
        'name' => 'Card Passenger',
        'roll' => 'STU-2026-001',
        'contact_number' => '03001234567',
        'passenger_type' => 'Student',
        'department' => 'Computer Science',
        'address' => 'Test Address',
        'emergency_contact' => '03007654321',
        'status' => 'Active',
        'approval_status' => 'approved',
        'route_id' => $route->id,
        'stop_id' => $mainStop->id,
        'stop' => $mainStop->name,
        'pickup_time' => '08:10',
        'dropoff_time' => '16:30',
    ]);

    $pngBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl4pGQAAAAASUVORK5CYII=');
    $tmpPath = tempnam(sys_get_temp_dir(), 'passenger-photo-');
    file_put_contents($tmpPath, $pngBytes);
    $photo = new UploadedFile($tmpPath, 'profile.png', 'image/png', null, true);

    $this->actingAs($user)
        ->post(route('passengers.updateOwnPhoto'), ['photo' => $photo])
        ->assertRedirect();

    $passenger->refresh();
    expect($passenger->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($passenger->photo_path);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Download Card')
        ->assertSee(Storage::disk('public')->url($passenger->photo_path), false);

    $this->actingAs($user)
        ->get(route('passengers.transport-card', $passenger))
        ->assertOk()
        ->assertSee('Download Front PNG')
        ->assertSee('Download Back PNG')
        ->assertSee('Campus Express')
        ->assertSee('Main Gate')
        ->assertSee('Library Stop')
        ->assertSee('STU-2026-001');
});

test('transport card is only available to approved passenger owner or authorized staff', function () {
    $owner = User::create([
        'name' => 'Pending Passenger',
        'email' => 'pending-card@example.test',
        'password' => bcrypt('password123'),
        'role' => 'passenger',
    ]);

    $pending = Passenger::create([
        'user_id' => $owner->id,
        'name' => 'Pending Passenger',
        'roll' => 'PENDING-CARD',
        'status' => 'Inactive',
        'approval_status' => 'pending',
    ]);

    $this->actingAs($owner)
        ->get(route('passengers.transport-card', $pending))
        ->assertForbidden();

    $otherUser = User::create([
        'name' => 'Other Passenger',
        'email' => 'other-card@example.test',
        'password' => bcrypt('password123'),
        'role' => 'passenger',
    ]);

    $approved = Passenger::create([
        'user_id' => $otherUser->id,
        'name' => 'Other Passenger',
        'roll' => 'OTHER-CARD',
        'status' => 'Active',
        'approval_status' => 'approved',
    ]);

    $this->actingAs($owner)
        ->get(route('passengers.transport-card', $approved))
        ->assertForbidden();
});
