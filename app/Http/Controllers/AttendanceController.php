<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Passenger;
use App\Models\DailyAssignment;
use Illuminate\Support\Facades\Hash;

class AttendanceController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $stats = [
            'present'     => Attendance::where('date', $today)->where('status', 'Present')->count(),
            'absent'      => Attendance::where('date', $today)->where('status', 'Absent')->count(),
            'total'       => Passenger::where('status', 'Active')->count(),
            'qr_scans'    => Attendance::where('date', $today)->where('method', 'qr')->count(),
            'fingerprints'=> Attendance::where('date', $today)->where('method', 'fingerprint')->count(),
        ];

        $attendance = Attendance::with('passenger.route')
            ->where('date', $today)
            ->latest()
            ->get();

        $passengers = Passenger::where('status', 'Active')->orderBy('name')->get();

        return view('attendance.index', compact('stats', 'attendance', 'passengers'));
    }

    // Manual / fingerprint attendance via form
    public function store(Request $request)
    {
        $request->validate([
            'passenger_id'     => 'required|exists:passengers,id',
            'date'             => 'required|date',
            'status'           => 'required|string|in:Present,Absent',
            'time'             => 'nullable|string',
            'method'           => 'nullable|string|in:manual,qr,fingerprint',
            'fingerprint_data' => 'nullable|string',
        ]);

        $method = $request->method ?? 'manual';

        $passenger = Passenger::findOrFail($request->passenger_id);

        if (!$passenger->qrIsActive()) {
            $msg = 'Attendance cannot be marked: transport application is not approved or the monthly fee is unpaid.';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $msg], 422)
                : back()->withErrors(['passenger_id' => $msg])->withInput();
        }

        // If fingerprint method, verify the hash
        if ($method === 'fingerprint') {
            if (!$passenger->fingerprint_enrolled
                || !$request->fingerprint_data
                || !Hash::check($request->fingerprint_data, $passenger->fingerprint_hash)) {
                return back()->withErrors(['fingerprint_data' => 'Security PIN did not match. Please try again.'])->withInput();
            }
        }

        Attendance::updateOrCreate(
            ['passenger_id' => $request->passenger_id, 'date' => $request->date],
            [
                'status' => $request->status,
                'time'   => $request->time ?? now()->format('H:i'),
                'method' => $method,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Attendance recorded via ' . $method]);
        }

        return redirect()->route('attendance.index')
            ->with('success', 'Attendance recorded via ' . ucfirst($method) . '!');
    }

    // QR Code scan endpoint — called when passenger QR is scanned
    public function scanQr(string $token)
    {
        $passenger = Passenger::where('qr_token', $token)
            ->where('status', 'Active')
            ->firstOrFail();

        if (!$passenger->qrIsActive()) {
            return view('attendance.qr-result', [
                'passenger'  => $passenger,
                'status'     => 'inactive',
                'attendance' => null,
            ]);
        }

        $today = now()->toDateString();

        // Check if already marked today
        $existing = Attendance::where('passenger_id', $passenger->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            return view('attendance.qr-result', [
                'passenger' => $passenger,
                'status'    => 'already',
                'attendance'=> $existing,
            ]);
        }

        // Mark present via QR
        $attendance = Attendance::create([
            'passenger_id' => $passenger->id,
            'date'         => $today,
            'status'       => 'Present',
            'time'         => now()->format('H:i'),
            'method'       => 'qr',
        ]);

        return view('attendance.qr-result', [
            'passenger'  => $passenger,
            'status'     => 'success',
            'attendance' => $attendance,
        ]);
    }

    // Human-readable remark shown on the scanner UI for each outcome status.
    private const STATUS_LABELS = [
        'success'     => 'Attendance Marked Successfully',
        'already'     => 'Already Marked',
        'fee_pending' => 'Fee Pending',
        'not_active'  => 'Pass Inactive',
        'not_found'   => 'Passenger Not Found',
        'invalid'     => 'Invalid QR Code',
        'wrong_route' => 'Wrong Bus / Route',
        'error'       => 'Attendance Failed',
    ];

    /** Passenger + fee/pass/attendance snapshot shown on the scanner result card. */
    private function passengerCard(Passenger $passenger, ?Attendance $attendance = null): array
    {
        $attendance = $attendance ?: Attendance::where('passenger_id', $passenger->id)
            ->where('date', now()->toDateString())
            ->first();

        return [
            'name'              => $passenger->name,
            'roll'              => $passenger->roll,
            'department'        => $passenger->department,
            'route'             => $passenger->route->name ?? null,
            'stop'              => $passenger->stop,
            'attendance_status' => $attendance->status ?? 'Not Marked',
            'attendance_time'   => $attendance ? \Carbon\Carbon::parse($attendance->time)->format('h:i A') : null,
            'fee_status'        => $passenger->activeFeePayment() ? 'Paid' : 'Pending',
            'pass_status'       => $passenger->qrIsActive() ? 'Active' : 'Inactive',
        ];
    }

    private function scanResult(string $status, string $message, array $extra = [], int $code = 200)
    {
        return response()->json(array_merge([
            'success'      => in_array($status, ['success', 'already'], true),
            'status'       => $status,
            'status_label' => self::STATUS_LABELS[$status] ?? self::STATUS_LABELS['error'],
            'message'      => $message,
        ], $extra), $code);
    }

    /**
     * The schedule overrides the passenger's normal route for scanner checks.
     * If today's schedule has an explicit passenger list, only passengers on that
     * list may be marked on the selected bus. Older schedules with no passenger
     * rows fall back to the passenger's permanent route for compatibility.
     */
    private function passengerAllowedOnRouteToday(Passenger $passenger, int $routeId): bool
    {
        $assignment = DailyAssignment::with('passengerAssignments')
            ->whereDate('date', today())
            ->where('route_id', $routeId)
            ->where('status', '!=', 'Cancelled')
            ->first();

        if ($assignment && $assignment->passengerAssignments->isNotEmpty()) {
            return $assignment->passengerAssignments->contains('passenger_id', $passenger->id);
        }

        return (int) $passenger->route_id === $routeId;
    }

    // AJAX: called by the live camera scanner (Scanner tab) on every decoded QR frame
    public function scanQrCamera(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'route_id' => 'nullable|integer|exists:routes,id',
        ]);

        $passenger = Passenger::where('qr_token', $request->token)
            ->where('status', 'Active')
            ->first();

        if (!$passenger) {
            return $this->scanResult('invalid', 'Invalid QR Code — this pass is not recognized.', [], 404);
        }

        // When the dedicated Scanner Terminal selected a bus first, prevent a
        // passenger from a different route being marked on the wrong shuttle.
        // The generic Attendance scanner does not send route_id, so its
        // existing behaviour remains unchanged.
        if ($request->filled('route_id') && !$this->passengerAllowedOnRouteToday($passenger, (int) $request->route_id)) {
            $selectedRoute = \App\Models\Route::find($request->route_id);
            $passengerRoute = $passenger->route?->name ?: 'another route';
            $selectedRouteName = $selectedRoute?->name ?: 'the selected route';

            return $this->scanResult(
                'wrong_route',
                $passenger->name . ' is assigned to ' . $passengerRoute . ', not ' . $selectedRouteName . '.',
                ['passenger' => $this->passengerCard($passenger)],
                422
            );
        }

        // Distinguish *why* the pass is inactive so the scanner can show a precise remark.
        if (!$passenger->isApproved()) {
            return $this->scanResult('not_active', 'Pass Inactive — ' . $passenger->name . '\'s transport application has not been approved yet.', [
                'passenger' => $this->passengerCard($passenger),
            ], 422);
        }

        if (!$passenger->activeFeePayment()) {
            return $this->scanResult('fee_pending', 'Fee Pending — ' . $passenger->name . '\'s monthly fee has not been paid/approved.', [
                'passenger' => $this->passengerCard($passenger),
            ], 422);
        }

        $today = now()->toDateString();

        $existing = Attendance::where('passenger_id', $passenger->id)
            ->where('date', $today)
            ->first();

        if ($existing) {
            return $this->scanResult('already', $passenger->name . ' was already marked ' . $existing->status . ' today at ' . \Carbon\Carbon::parse($existing->time)->format('h:i A') . '.', [
                'passenger' => $this->passengerCard($passenger, $existing),
            ]);
        }

        $attendance = Attendance::create([
            'passenger_id' => $passenger->id,
            'date'         => $today,
            'status'       => 'Present',
            'time'         => now()->format('H:i'),
            'method'       => 'qr',
        ]);

        return $this->scanResult('success', 'Attendance marked successfully for ' . $passenger->name . '.', [
            'passenger' => $this->passengerCard($passenger, $attendance),
        ]);
    }

    // AJAX: verify Security PIN and mark attendance.
    // The normal Attendance screen sends passenger_id. The dedicated Scanner
    // Terminal can instead send the passenger's roll number plus the selected
    // route, so staff never need to expose or search internal passenger IDs.
    public function fingerprintVerify(Request $request)
    {
        $request->validate([
            'passenger_id'     => 'nullable|integer|exists:passengers,id|required_without:roll',
            'roll'             => 'nullable|string|max:100|required_without:passenger_id',
            'route_id'         => 'nullable|integer|exists:routes,id',
            'fingerprint_data' => 'required|string|min:6|max:20',
        ]);

        if ($request->filled('passenger_id')) {
            $passenger = Passenger::with('route')->find($request->passenger_id);
        } else {
            $roll = trim((string) $request->roll);
            $passenger = Passenger::with('route')
                ->whereRaw('LOWER(roll) = ?', [strtolower($roll)])
                ->first();
        }

        if (!$passenger) {
            return $this->scanResult('not_found', 'Passenger Not Found — check the Passenger ID / Roll No and try again.', [], 404);
        }

        // The Scanner Terminal always sends route_id after a bus is selected.
        // This prevents a valid PIN being used to mark attendance on the wrong bus.
        if ($request->filled('route_id') && !$this->passengerAllowedOnRouteToday($passenger, (int) $request->route_id)) {
            $selectedRoute = \App\Models\Route::find($request->route_id);
            $passengerRoute = $passenger->route?->name ?: 'another route';
            $selectedRouteName = $selectedRoute?->name ?: 'the selected route';

            return $this->scanResult(
                'wrong_route',
                $passenger->name . ' is assigned to ' . $passengerRoute . ', not ' . $selectedRouteName . '.',
                ['passenger' => $this->passengerCard($passenger)],
                422
            );
        }

        if ($passenger->status !== 'Active' || !$passenger->isApproved()) {
            return $this->scanResult('not_active', 'Pass Inactive — ' . $passenger->name . '\'s transport application is not active.', [
                'passenger' => $this->passengerCard($passenger),
            ], 422);
        }

        if (!$passenger->activeFeePayment()) {
            return $this->scanResult('fee_pending', 'Fee Pending — ' . $passenger->name . '\'s monthly fee has not been paid/approved.', [
                'passenger' => $this->passengerCard($passenger),
            ], 422);
        }

        if (!$passenger->fingerprint_enrolled || !$passenger->fingerprint_hash) {
            return $this->scanResult('error', 'Attendance Failed — no Security PIN is registered for this passenger.', [
                'passenger' => $this->passengerCard($passenger),
            ], 422);
        }

        if (!Hash::check($request->fingerprint_data, $passenger->fingerprint_hash)) {
            return $this->scanResult('error', 'Attendance Failed — Security PIN did not match. Please try again.', [
                'passenger' => $this->passengerCard($passenger),
            ], 422);
        }

        $today = now()->toDateString();

        $existing = Attendance::where('passenger_id', $passenger->id)->where('date', $today)->first();
        if ($existing) {
            return $this->scanResult('already', $passenger->name . ' was already marked ' . $existing->status . ' today at ' . \Carbon\Carbon::parse($existing->time)->format('h:i A') . '.', [
                'passenger' => $this->passengerCard($passenger, $existing),
            ]);
        }

        $attendance = Attendance::updateOrCreate(
            ['passenger_id' => $passenger->id, 'date' => $today],
            ['status' => 'Present', 'time' => now()->format('H:i'), 'method' => 'fingerprint']
        );

        return $this->scanResult('success', 'Attendance marked successfully for ' . $passenger->name . ' via Security PIN.', [
            'passenger' => $this->passengerCard($passenger, $attendance),
        ]);
    }
}
