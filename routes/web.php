<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PassengerController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FeePaymentController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DailyAssignmentController;
use App\Http\Controllers\ScannerController;

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
| Public QR attendance scan
|--------------------------------------------------------------------------
| Hit by a phone camera / browser when a passenger's printed pass is
| scanned. Rate-limited to blunt automated abuse of a leaked/shared token.
| See README "Known limitations" for the static-token trade-off.
*/
Route::get('/scan/{token}', [AttendanceController::class, 'scanQr'])
    ->middleware('throttle:30,1')
    ->name('attendance.scan');

/*
|--------------------------------------------------------------------------
| Authenticated app
|--------------------------------------------------------------------------
| Permission model:
|   - Admin can manage Transport Incharges, drivers, passengers, buses, routes
|     and the schedule.
|   - Transport Incharge can manage day-to-day transport operations including
|     drivers/passengers/buses/routes and the schedule.
|   - Driver sees their own assignments and attendance/messaging tools.
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /* Dedicated scanner terminal: bus lookup first, camera second. */
    Route::middleware('role:scanner')->group(function () {
        Route::get('/scanner', [ScannerController::class, 'index'])->name('scanner.index');
        Route::get('/scanner/bus-details', [ScannerController::class, 'busDetails'])
            ->middleware('throttle:60,1')
            ->name('scanner.busDetails');
    });

    /*
    |--------------------------------------------------------------------
    | SHARED VIEWS — admin & incharge can both see these listing/detail pages
    |--------------------------------------------------------------------
    */
    Route::middleware('role:admin,incharge')->group(function () {
        Route::get('/vehicles',   [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/drivers',    [DriverController::class, 'index'])->name('drivers.index');
        // NOTE: /drivers/create MUST be registered before the /drivers/{driver}
        // wildcard below — otherwise Laravel matches "create" as the {driver}
        // route-model-binding parameter and 404s (no Driver with that key).
        Route::get('/drivers/create',        [DriverController::class, 'create'])->name('drivers.create');
        Route::get('/drivers/{driver}',            [DriverController::class, 'show'])->name('drivers.show');
        Route::get('/drivers/{driver}/cnic/{side}', [DriverController::class, 'showCnic'])->name('drivers.cnic');
        Route::get('/passengers', [PassengerController::class, 'index'])->name('passengers.index');
        Route::get('/tmsroutes',  [RouteController::class, 'index'])->name('tmsroutes.index');
        // Schedule board — both Admin and Incharge can manage it below
        Route::get('/schedule', [DailyAssignmentController::class, 'index'])->name('schedule.index');
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/complaints',    [ComplaintController::class, 'index'])->name('complaints.index');
        // Admin and Incharge can both review and respond to passenger complaints/feedback.
        Route::post('/complaints/{complaint}/respond', [ComplaintController::class, 'respond'])->name('complaints.respond');
        Route::get('/fee-payments',  [FeePaymentController::class, 'index'])->name('fee-payments.index');
        // Attendance is view-only for Admin & Incharge here — daily/weekly/monthly/yearly
        // report only. Marking attendance itself is a separate, more restricted screen below.
        Route::get('/reports/attendance', [ReportController::class, 'index'])->name('reports.attendance');
    });

    /*
    |--------------------------------------------------------------------
    | OPERATIONAL MANAGEMENT — Admin + Transport Incharge.
    | Both roles can maintain buses, routes, drivers, passengers and the schedule.
    |--------------------------------------------------------------------
    */
    Route::middleware('role:admin,incharge')->group(function () {

        // Vehicles / buses
        Route::get('/vehicles/create',         [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/vehicles',               [VehicleController::class, 'store'])->name('vehicles.store');
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehicles/{vehicle}',      [VehicleController::class, 'update'])->name('vehicles.update');
        Route::delete('/vehicles/{vehicle}',   [VehicleController::class, 'destroy'])->name('vehicles.destroy');

        // Drivers — create/edit/reset-password (delete is shared with admin, see below)
        // (drivers.create is registered earlier, above, ahead of the /drivers/{driver} wildcard)
        Route::post('/drivers',              [DriverController::class, 'store'])->name('drivers.store');
        Route::get('/drivers/{driver}/edit', [DriverController::class, 'edit'])->name('drivers.edit');
        Route::put('/drivers/{driver}',      [DriverController::class, 'update'])->name('drivers.update');
        Route::post('/drivers/{driver}/reset-password', [DriverController::class, 'resetPassword'])->name('drivers.reset-password');

        // Passenger record management (create / edit / approve / reject) — delete is shared with admin, see below
        Route::get('/passengers/create',           [PassengerController::class, 'create'])->name('passengers.create');
        Route::post('/passengers',                 [PassengerController::class, 'store'])->name('passengers.store');
        Route::get('/passengers/{passenger}/edit',  [PassengerController::class, 'edit'])->name('passengers.edit');
        Route::put('/passengers/{passenger}',       [PassengerController::class, 'update'])->name('passengers.update');
        Route::post('/passengers/{passenger}/approve', [PassengerController::class, 'approve'])->name('passengers.approve');
        Route::post('/passengers/{passenger}/reject',  [PassengerController::class, 'reject'])->name('passengers.reject');
        Route::post('/passengers/{passenger}/regenerate-qr', [PassengerController::class, 'regenerateQr'])->name('passengers.regenerateQr');
        Route::post('/passengers/{passenger}/cancellation/approve', [PassengerController::class, 'approveCancellation'])->name('passengers.cancellation.approve');
        Route::post('/passengers/{passenger}/cancellation/reject',  [PassengerController::class, 'rejectCancellation'])->name('passengers.cancellation.reject');


        // Routes & stops
        Route::get('/tmsroutes/create',          [RouteController::class, 'create'])->name('tmsroutes.create');
        Route::post('/tmsroutes',                [RouteController::class, 'store'])->name('tmsroutes.store');
        Route::get('/tmsroutes/{tmsroute}/edit', [RouteController::class, 'edit'])->name('tmsroutes.edit');
        Route::put('/tmsroutes/{tmsroute}',      [RouteController::class, 'update'])->name('tmsroutes.update');
        Route::delete('/tmsroutes/{tmsroute}',   [RouteController::class, 'destroy'])->name('tmsroutes.destroy');

        // Schedule — manually created per route/date; edited in place until an
        // Admin/Incharge intentionally changes it (never auto-regenerated).
        Route::get('/schedule/create',            [DailyAssignmentController::class, 'create'])->name('schedule.create');
        Route::post('/schedule',                   [DailyAssignmentController::class, 'store'])->name('schedule.store');
        Route::get('/schedule/{assignment}/edit',  [DailyAssignmentController::class, 'edit'])->name('schedule.edit');
        Route::put('/schedule/{assignment}',       [DailyAssignmentController::class, 'update'])->name('schedule.update');
        Route::delete('/schedule/{assignment}',    [DailyAssignmentController::class, 'destroy'])->name('schedule.destroy');

    });

    // Existing day-to-day office controls remain Transport Incharge-only.
    Route::middleware('role:incharge')->group(function () {
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');

        Route::put('/fee-payments/settings', [FeePaymentController::class, 'updateSettings'])->name('fee-payments.settings');
        Route::post('/fee-payments/{feePayment}/approve', [FeePaymentController::class, 'approve'])->name('fee-payments.approve');
        Route::post('/fee-payments/{feePayment}/reject', [FeePaymentController::class, 'reject'])->name('fee-payments.reject');
    });

    /*
    |--------------------------------------------------------------------
    | ACCOUNT REMOVAL — Admin and Incharge can remove driver/passenger records.
    |--------------------------------------------------------------------
    */
    Route::middleware('role:admin,incharge')->group(function () {
        Route::delete('/passengers/{passenger}', [PassengerController::class, 'destroy'])->name('passengers.destroy');
        Route::delete('/drivers/{driver}',       [DriverController::class, 'destroy'])->name('drivers.destroy');
    });

    // Transport Incharge account management — Admin only.
    Route::middleware('role:admin')->group(function () {
        Route::get('/staff',              [StaffController::class, 'index'])->name('staff.index');
        Route::get('/staff/create',       [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff',             [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{user}',       [StaffController::class, 'show'])->name('staff.show');
        Route::get('/staff/{user}/edit',  [StaffController::class, 'edit'])->name('staff.edit');
        Route::put('/staff/{user}',       [StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{user}',    [StaffController::class, 'destroy'])->name('staff.destroy');
    });

    /*
    |--------------------------------------------------------------------
    | A single passenger's detail page — staff-or-owner, enforced by
    | PassengerPolicy@view via the `can` middleware below.
    |--------------------------------------------------------------------
    */
    Route::get('/passengers/{passenger}', [PassengerController::class, 'show'])
        ->middleware('can:view,passenger')
        ->name('passengers.show');

    Route::get('/passengers/{passenger}/qr', [PassengerController::class, 'downloadQr'])
        ->middleware('can:view,passenger')
        ->name('passengers.qr');

    Route::post('/passengers/{passenger}/cancellation', [PassengerController::class, 'requestCancellation'])
        ->middleware('can:view,passenger')
        ->name('passengers.cancellation.request');

    /*
    |--------------------------------------------------------------------
    | Complaints & feedback — passenger self-service (submit only)
    |--------------------------------------------------------------------
    */
    Route::middleware('role:passenger')->group(function () {
        Route::get('/complaints/create', [ComplaintController::class, 'create'])->name('complaints.create');
        Route::post('/complaints',       [ComplaintController::class, 'store'])->name('complaints.store');
    });

    /*
    |--------------------------------------------------------------------
    | Messaging — a driver talks with "the office" (any admin/incharge).
    | Driver sees only their own thread; admin/incharge pick a driver first.
    | Admin can read/participate (view-only elsewhere doesn't restrict
    | communicating with drivers).
    |--------------------------------------------------------------------
    */
    // Driver self-service — upload/replace their own profile picture from their dashboard.
    Route::middleware('role:driver')->group(function () {
        Route::post('/my-profile/photo', [DriverController::class, 'updateOwnPhoto'])->name('drivers.updateOwnPhoto');
        Route::post('/my-profile/password', [DriverController::class, 'updateOwnPassword'])->middleware('throttle:5,1')->name('drivers.updateOwnPassword');
    });

    Route::middleware('role:admin,incharge,driver')->group(function () {
        Route::get('/messages',                    [MessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/{driverUserId}',        [MessageController::class, 'thread'])->name('messages.thread');
        Route::post('/messages/{driverUserId}',       [MessageController::class, 'store'])->name('messages.store');
    });

    /*
    |--------------------------------------------------------------------
    | Attendance marking — staff who actually run the shuttle (incharge,
    | scanner). Drivers no longer have an attendance module on their
    | dashboard/portal. Passengers never mark their own attendance from
    | this screen. Admin does NOT mark attendance — Admin only sees the
    | daily/weekly/monthly/yearly attendance report (see reports.attendance
    | above, under the Admin+Incharge view-only group).
    |--------------------------------------------------------------------
    */
    Route::middleware('role:incharge,scanner')->group(function () {
        Route::get('/attendance',  [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/fingerprint-verify', [AttendanceController::class, 'fingerprintVerify'])
            ->middleware('throttle:10,1')
            ->name('attendance.fingerprint');
        Route::post('/attendance/scan-camera', [AttendanceController::class, 'scanQrCamera'])
            ->middleware('throttle:60,1')
            ->name('attendance.scanCamera');
    });

    /*
    |--------------------------------------------------------------------
    | Passenger self-service — pay the monthly transport fee, view own
    | status/history. Ownership of the passenger record is enforced inside
    | the controller (looked up by the logged-in user's id), so this only
    | needs to be behind plain auth.
    |--------------------------------------------------------------------
    */
    Route::get('/fee-payments/{feePayment}/screenshot', [FeePaymentController::class, 'showScreenshot'])->name('fee-payments.screenshot');

    Route::get('/my-fee',       [FeePaymentController::class, 'myFee'])->name('fee.my');
    Route::post('/my-fee/pay',  [FeePaymentController::class, 'pay'])->middleware('throttle:10,1')->name('fee.pay');

    // Stripe sandbox/test-mode card payment for the transport fee
    Route::post('/my-fee/pay/stripe',         [FeePaymentController::class, 'stripeCheckout'])->middleware('throttle:10,1')->name('fee.stripe.checkout');
    Route::get('/my-fee/pay/stripe/success',  [FeePaymentController::class, 'stripeSuccess'])->name('fee.stripe.success');
    Route::get('/my-fee/pay/stripe/cancel',   [FeePaymentController::class, 'stripeCancel'])->name('fee.stripe.cancel');
});
