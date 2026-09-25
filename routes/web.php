<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\StripeWebhookController;

/*
|--------------------------------------------------------------------------
| Stripe webhook — unauthenticated, verified by Stripe signature instead.
| CSRF is exempted for this single path in bootstrap/app.php.
|--------------------------------------------------------------------------
*/
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->name('stripe.webhook');

/*
|--------------------------------------------------------------------------
| Authentication (throttled to prevent brute-force / credential stuffing)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/',          [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Authenticated app
|--------------------------------------------------------------------------
| Permission model:
|   - Admin owns system configuration, master-data management, approvals,
|     payment controls, announcements/complaints and staff management.
|   - Transport Incharge has limited day-to-day operational access:
|     Schedule, driver messaging and read-only operational lookups.
|   - Driver sees their own Schedule and messaging tools.
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------
    | OPERATIONAL READ ACCESS — Admin + Incharge
    |--------------------------------------------------------------------
    */
    Route::middleware('role:admin,incharge')->group(function () {
        Route::get('/vehicles',   [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/drivers',    [DriverController::class, 'index'])->name('drivers.index');
        Route::get('/passengers', [PassengerController::class, 'index'])->name('passengers.index');
        Route::get('/tmsroutes',  [RouteController::class, 'index'])->name('tmsroutes.index');
        Route::get('/schedule',   [ScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/fee-payments', [FeePaymentController::class, 'index'])->name('fee-payments.index');

        // Announcements — Incharge has read-only view access; only Admin can
        // publish/edit/delete (see the Admin-only group below for those).
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    });

    /*
    |--------------------------------------------------------------------
    | SCHEDULE OPERATIONS — Incharge only
    |--------------------------------------------------------------------
    | The Schedule is persistent and changes only through these explicit
    | create/update/delete actions; it does not rotate by date. Admin has
    | read-only access (see the Admin + Incharge read-access group above);
    | only the Transport Incharge has authority to create/edit/delete it.
    */
    Route::middleware('role:incharge')->group(function () {
        Route::get('/schedule/create',              [ScheduleController::class, 'create'])->name('schedule.create');
        Route::post('/schedule',                    [ScheduleController::class, 'store'])->name('schedule.store');
        Route::get('/schedule/{schedule}/edit',     [ScheduleController::class, 'edit'])->name('schedule.edit');
        Route::put('/schedule/{schedule}',          [ScheduleController::class, 'update'])->name('schedule.update');
        Route::delete('/schedule/{schedule}',       [ScheduleController::class, 'destroy'])->name('schedule.destroy');
   Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
        });

    /*
    |--------------------------------------------------------------------
    | FEE PAYMENT REVIEW — Incharge only
    |--------------------------------------------------------------------
    | Incharge approves/rejects submitted payments. Admin only configures
    | the fee amount/payment details (see fee-payments.settings below).
    */
    Route::middleware('role:incharge')->group(function () {
        Route::post('/fee-payments/{feePayment}/approve', [FeePaymentController::class, 'approve'])->name('fee-payments.approve');
        Route::post('/fee-payments/{feePayment}/reject', [FeePaymentController::class, 'reject'])->name('fee-payments.reject');
    
    });

    /*

    |--------------------------------------------------------------------
    | ADMINISTRATIVE MANAGEMENT — Admin only
    |--------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        // Vehicles / buses
        Route::get('/vehicles/create',         [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/vehicles',               [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehicles/{vehicle}',      [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('/vehicles/{vehicle}',   [VehicleController::class, 'destroy'])->name('vehicles.destroy');

        // Drivers
        Route::get('/drivers/create',        [DriverController::class, 'create'])->name('drivers.create');
        Route::post('/drivers',              [DriverController::class, 'store'])->name('drivers.store');
        Route::get('/drivers/{driver}/edit', [DriverController::class, 'edit'])->name('drivers.edit');
        Route::put('/drivers/{driver}',      [DriverController::class, 'update'])->name('drivers.update');
        Route::post('/drivers/{driver}/reset-password', [DriverController::class, 'resetPassword'])->name('drivers.reset-password');
        Route::delete('/drivers/{driver}',   [DriverController::class, 'destroy'])->name('drivers.destroy');
        // Passenger record / enrollment management
        Route::get('/passengers/create',            [PassengerController::class, 'create'])->name('passengers.create');
        Route::post('/passengers',                  [PassengerController::class, 'store'])->name('passengers.store');
        Route::get('/passengers/{passenger}/edit',  [PassengerController::class, 'edit'])->name('passengers.edit');
        Route::put('/passengers/{passenger}',       [PassengerController::class, 'update'])->name('passengers.update');
        Route::post('/passengers/{passenger}/approve', [PassengerController::class, 'approve'])->name('passengers.approve');
        Route::post('/passengers/{passenger}/reject',  [PassengerController::class, 'reject'])->name('passengers.reject');
        Route::post('/passengers/{passenger}/cancellation/approve', [PassengerController::class, 'approveCancellation'])->name('passengers.cancellation.approve');
        Route::post('/passengers/{passenger}/cancellation/reject',  [PassengerController::class, 'rejectCancellation'])->name('passengers.cancellation.reject');
        Route::delete('/passengers/{passenger}', [PassengerController::class, 'destroy'])->name('passengers.destroy');

        // Routes & stops
        Route::get('/tmsroutes/create',          [RouteController::class, 'create'])->name('tmsroutes.create');
        Route::post('/tmsroutes',                [RouteController::class, 'store'])->name('tmsroutes.store');
        Route::get('/tmsroutes/{tmsroute}/edit', [RouteController::class, 'edit'])->name('tmsroutes.edit');
        Route::put('/tmsroutes/{tmsroute}',      [RouteController::class, 'update'])->name('tmsroutes.update');
        Route::delete('/tmsroutes/{tmsroute}',   [RouteController::class, 'destroy'])->name('tmsroutes.destroy');

        // Fee configuration — Admin only sets the fee (amount/payment details).
        // Approving/rejecting submitted payments is Incharge's job (see the
        // Incharge-only group below).
        Route::put('/fee-payments/settings', [FeePaymentController::class, 'updateSettings'])->name('fee-payments.settings');

        // Announcements — Admin full management (create/edit/delete). The
        // read-only listing route (announcements.index) lives above, in the
        // Admin + Incharge operational read-access group.
        

        // Complaints & feedback — Admin full management
        Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/{complaint}/edit', [ComplaintController::class, 'edit'])->name('complaints.edit');
        Route::put('/complaints/{complaint}', [ComplaintController::class, 'update'])->name('complaints.update');
        Route::post('/complaints/{complaint}/respond', [ComplaintController::class, 'respond'])->name('complaints.respond');
        Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy'])->name('complaints.destroy');

        // Transport Incharge account management
        Route::get('/staff',             [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create',      [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff',            [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{user}',      [StaffController::class, 'show'])->name('staff.show');
        Route::get('/staff/{user}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{user}',      [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{user}',   [StaffController::class, 'destroy'])->name('staff.destroy');
    
        });

    /*
    |--------------------------------------------------------------------
    | Driver detail pages — Admin + Incharge (read-only lookups)
    |--------------------------------------------------------------------
    | IMPORTANT: these wildcard routes must be registered AFTER
    | /drivers/create (above) — Laravel matches routes in registration
    | order, and a wildcard /drivers/{driver} placed earlier than
    | /drivers/create would swallow the "create" segment as a driver ID
    | and 404 (this was the original "Add Driver shows error" bug).
    */
    Route::middleware('role:admin,incharge')->group(function () {
        Route::get('/drivers/{driver}',             [DriverController::class, 'show'])->name('drivers.show');
        Route::get('/drivers/{driver}/cnic/{side}', [DriverController::class, 'showCnic'])->name('drivers.cnic');
    });

    /*
    |--------------------------------------------------------------------
    | A single passenger detail page — staff-or-owner, enforced by policy
    |--------------------------------------------------------------------
    */
    Route::get('/passengers/{passenger}', [PassengerController::class, 'show'])
        ->middleware('can:view,passenger')
        ->name('passengers.show');

    Route::get('/passengers/{passenger}/transport-card', [PassengerController::class, 'transportCard'])
        ->middleware('can:view,passenger')
        ->name('passengers.transport-card');

    Route::post('/passengers/{passenger}/cancellation', [PassengerController::class, 'requestCancellation'])
        ->middleware('can:view,passenger')
        ->name('passengers.cancellation.request');

    /* Complaint creation: Passenger self-service only — Admin only receives
       and manages (responds to / resolves) complaints, never creates them. */
    Route::middleware('role:passenger')->group(function () {
        Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
        Route::post('/complaints',       [ComplaintController::class, 'store'])->name('complaints.store');
        Route::post('/my-passenger-profile/photo', [PassengerController::class, 'updateOwnPhoto'])->name('passengers.updateOwnPhoto');
    });

    /* Driver self-service profile actions. */
    Route::middleware('role:driver')->group(function () {
        Route::post('/my-profile/photo', [DriverController::class, 'updateOwnPhoto'])->name('drivers.updateOwnPhoto');
        Route::post('/my-profile/password', [DriverController::class, 'updateOwnPassword'])->middleware('throttle:5,1')->name('drivers.updateOwnPassword');
    });

    /* Driver messaging — Admin/Incharge coordinate with drivers. */
    Route::middleware('role:admin,incharge,driver')->group(function () {
        Route::get('/messages',                 [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{driverUserId}',  [MessageController::class, 'thread'])->name('messages.thread');
        Route::post('/messages/{driverUserId}', [MessageController::class, 'store'])->name('messages.store');
    });

    /* Passenger fee self-service + authenticated receipt access. */
    Route::get('/fee-payments/{feePayment}/screenshot', [FeePaymentController::class, 'showScreenshot'])->name('fee-payments.screenshot');

    Route::get('/my-fee',      [FeePaymentController::class, 'myFee'])->name('fee.my');
    Route::post('/my-fee/pay', [FeePaymentController::class, 'pay'])->middleware('throttle:10,1')->name('fee.pay');

    // Existing Stripe checkout flow — unchanged.
    Route::post('/my-fee/pay/stripe',        [FeePaymentController::class, 'stripeCheckout'])->middleware('throttle:10,1')->name('fee.stripe.checkout');
    Route::get('/my-fee/pay/stripe/success', [FeePaymentController::class, 'stripeSuccess'])->name('fee.stripe.success');
    Route::get('/my-fee/pay/stripe/cancel',  [FeePaymentController::class, 'stripeCancel'])->name('fee.stripe.cancel');
});
