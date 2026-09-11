<?php

use App\Models\User;
use App\Models\Passenger;
use App\Models\FeePayment;
use App\Models\FeeSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function makeApprovedPassenger(string $emailPrefix): array
{
    static $counter = 0;
    $counter++;

    $user = User::create([
        'name'     => 'Passenger ' . $counter,
        'email'    => $emailPrefix . $counter . '@test.com',
        'password' => bcrypt('password123'),
        'role'     => 'passenger',
    ]);

    $passenger = Passenger::create([
        'user_id'              => $user->id,
        'name'                 => 'Passenger ' . $counter,
        'roll'                 => 'FEE-' . $counter,
        'department'           => 'CS',
        'status'               => 'Active',
        'approval_status'      => 'approved',
        'qr_token'             => Passenger::generateQrToken(),
        'fingerprint_hash'     => Hash::make('123456'),
        'fingerprint_enrolled' => true,
    ]);

    return [$user, $passenger];
}

test('fee settings page requires admin/incharge role', function () {
    [$user, $passenger] = makeApprovedPassenger('block');
    $this->actingAs($user)->get('/fee-payments')->assertForbidden();
});

test('admin can update the monthly fee amount', function () {
    $admin = User::create(['name' => 'A', 'email' => 'feeadmin@t.com', 'password' => bcrypt('x'), 'role' => 'admin']);

    $this->actingAs($admin)
        ->put('/fee-payments/settings', ['monthly_fee' => 1500])
        ->assertRedirect();

    expect(FeeSetting::amount())->toEqual(1500.0);
});

test('passenger cannot pay before their transport application is approved', function () {
    $user = User::create(['name' => 'Pending Guy', 'email' => 'pendingfee@t.com', 'password' => bcrypt('x'), 'role' => 'passenger']);
    Passenger::create([
        'user_id' => $user->id, 'name' => 'Pending Guy', 'roll' => 'FEE-PEND', 'department' => 'CS',
        'status' => 'Inactive', 'approval_status' => 'pending',
        'qr_token' => Passenger::generateQrToken(),
        'fingerprint_hash' => Hash::make('123456'), 'fingerprint_enrolled' => true,
    ]);

    Storage::fake('public');
    $resp = $this->actingAs($user)->post('/my-fee/pay', [
        'tid'        => 'TXN001',
        'screenshot' => UploadedFile::fake()->image('receipt.jpg'),
    ]);

    $resp->assertRedirect('/dashboard');
    expect(FeePayment::count())->toBe(0);
});

test('approved passenger can submit a fee payment for review', function () {
    [$user, $passenger] = makeApprovedPassenger('pay');
    FeeSetting::current()->update(['monthly_fee' => 1200]);
    Storage::fake('public');

    $resp = $this->actingAs($user)->post('/my-fee/pay', [
        'tid'        => 'TXN12345',
        'screenshot' => UploadedFile::fake()->image('receipt.jpg'),
    ]);

    $resp->assertRedirect('/my-fee');
    $this->assertDatabaseHas('fee_payments', [
        'passenger_id' => $passenger->id,
        'tid'          => 'TXN12345',
        'status'       => 'pending',
        'month'        => now()->format('Y-m'),
    ]);
    expect((float) FeePayment::first()->amount)->toEqual(1200.0);
});

test('passenger cannot submit a second payment while one is pending', function () {
    [$user, $passenger] = makeApprovedPassenger('dup');
    Storage::fake('public');

    $this->actingAs($user)->post('/my-fee/pay', [
        'tid' => 'TXN1', 'screenshot' => UploadedFile::fake()->image('a.jpg'),
    ]);

    $resp = $this->actingAs($user)->post('/my-fee/pay', [
        'tid' => 'TXN2', 'screenshot' => UploadedFile::fake()->image('b.jpg'),
    ]);

    $resp->assertRedirect();
    expect(FeePayment::count())->toBe(1);
});

test('admin approving a payment extends validity through end of that month', function () {
    [$user, $passenger] = makeApprovedPassenger('approve');
    $admin = User::create(['name' => 'A', 'email' => 'feeapprove@t.com', 'password' => bcrypt('x'), 'role' => 'admin']);

    $payment = FeePayment::create([
        'passenger_id'    => $passenger->id,
        'month'           => now()->format('Y-m'),
        'amount'          => 1000,
        'tid'             => 'TXN9',
        'screenshot_path' => 'fee-screenshots/fake.jpg',
        'status'          => 'pending',
    ]);

    $this->actingAs($admin)->post("/fee-payments/{$payment->id}/approve")->assertRedirect();

    $payment->refresh();
    expect($payment->status)->toBe('approved');
    expect($payment->qr_expires_at->toDateString())->toBe(now()->endOfMonth()->toDateString());
    expect($payment->isActive())->toBeTrue();
    expect($passenger->fresh()->qrIsActive())->toBeTrue();
});

test('admin rejecting a payment clears any validity window and records a reason', function () {
    [$user, $passenger] = makeApprovedPassenger('reject');
    $admin = User::create(['name' => 'A', 'email' => 'feereject@t.com', 'password' => bcrypt('x'), 'role' => 'admin']);

    $payment = FeePayment::create([
        'passenger_id'    => $passenger->id,
        'month'           => now()->format('Y-m'),
        'amount'          => 1000,
        'tid'             => 'TXN8',
        'screenshot_path' => 'fee-screenshots/fake.jpg',
        'status'          => 'pending',
    ]);

    $this->actingAs($admin)
        ->post("/fee-payments/{$payment->id}/reject", ['rejection_reason' => 'Screenshot unreadable'])
        ->assertRedirect();

    $payment->refresh();
    expect($payment->status)->toBe('rejected');
    expect($payment->rejection_reason)->toBe('Screenshot unreadable');
    expect($payment->qr_token)->toBeNull();
});

test('a passenger paying again after an active pass gets scheduled for the following month', function () {
    [$user, $passenger] = makeApprovedPassenger('advance');

    FeePayment::create([
        'passenger_id' => $passenger->id, 'month' => now()->format('Y-m'), 'amount' => 1000,
        'tid' => 'TXN-CUR', 'screenshot_path' => 'x.jpg', 'status' => 'approved',
        'qr_expires_at' => now()->endOfMonth(),
    ]);

    Storage::fake('public');
    $this->actingAs($user)->post('/my-fee/pay', [
        'tid' => 'TXN-NEXT', 'screenshot' => UploadedFile::fake()->image('c.jpg'),
    ])->assertRedirect('/my-fee');

    $next = FeePayment::where('tid', 'TXN-NEXT')->first();
    expect($next->month)->toBe(now()->addMonth()->format('Y-m'));
});
