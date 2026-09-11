<?php

namespace App\Http\Controllers;

use App\Models\DailyAssignment;
use App\Models\DailyAssignmentPassenger;
use App\Models\DailyAssignmentStop;
use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DailyAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $date = $this->resolveDate($request->query('date'));

        $assignments = DailyAssignment::with([
                'route.routeStops', 'driver', 'vehicle',
                'stops.routeStop', 'passengerAssignments.passenger',
            ])
            ->whereDate('date', $date)
            ->get()
            ->sortBy(fn ($a) => $a->route->name ?? '');

        $unassignedRoutes = Route::with('vehicle.driver')
            ->where('status', 'Active')
            ->whereNotIn('id', $assignments->pluck('route_id'))
            ->orderBy('name')
            ->get();

        return view('schedule.index', compact('assignments', 'date', 'unassignedRoutes'));
    }

    public function create(Request $request)
    {
        $date = $this->resolveDate($request->query('date'));
        $routes = Route::with('routeStops')->where('status', 'Active')->orderBy('name')->get();

        $selectedRoute = null;
        if ($request->filled('route_id')) {
            $selectedRoute = Route::with(['routeStops', 'vehicle.driver'])
                ->where('status', 'Active')
                ->find($request->query('route_id'));
        }

        $drivers = Driver::where('status', 'Active')->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'Active')->orderBy('number')->get();
        $students = Passenger::with(['route', 'routeStop'])
            ->where('approval_status', 'approved')
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        return view('schedule.create', compact(
            'date', 'routes', 'selectedRoute', 'drivers', 'vehicles', 'students'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateAssignment($request);
        $this->validateAssignmentDetails($validated, $request);
        $existingAssignment = DailyAssignment::whereDate('date', $validated['date'])
            ->where('route_id', $validated['route_id'])
            ->first();
        $this->ensureResourcesAvailable($validated, $existingAssignment?->id);

        $assignment = DB::transaction(function () use ($validated, $request) {
            $passengerIds = collect($request->input('passenger_ids', []))->filter()->unique()->values();

            $assignment = DailyAssignment::updateOrCreate(
                ['date' => $validated['date'], 'route_id' => $validated['route_id']],
                [
                    'driver_id'                => $validated['driver_id'],
                    'vehicle_id'               => $validated['vehicle_id'],
                    'estimated_passengers'     => $passengerIds->count(),
                    'estimated_departure_time' => $validated['estimated_departure_time'] ?? null,
                    'notes'                    => $validated['notes'] ?? null,
                    'status'                   => $validated['status'],
                    'created_by'               => Auth::id(),
                ]
            );

            $this->syncStops($assignment, $request);
            $this->syncPassengers($assignment, $request);

            return $assignment->fresh();
        });

        return redirect()->route('schedule.index', ['date' => $assignment->date->toDateString()])
            ->with('success', 'Schedule saved with driver, bus, stops, passengers and timings.');
    }

    public function edit(Request $request, DailyAssignment $assignment)
    {
        $assignment->load([
            'route.routeStops', 'stops.routeStop',
            'passengerAssignments.passenger.route', 'passengerAssignments.routeStop',
        ]);

        $drivers = Driver::where('status', 'Active')
            ->orWhere('id', $assignment->driver_id)
            ->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'Active')
            ->orWhere('id', $assignment->vehicle_id)
            ->orderBy('number')->get();
        $currentStudentIds = $assignment->passengerAssignments->pluck('passenger_id');
        $students = Passenger::with(['route', 'routeStop'])
            ->where('approval_status', 'approved')
            ->where(function ($q) use ($currentStudentIds) {
                $q->where('status', 'Active')->orWhereIn('id', $currentStudentIds);
            })
            ->orderBy('name')
            ->get();

        $routes = Route::with(['routeStops', 'vehicle.driver'])
            ->where('status', 'Active')
            ->orWhere('id', $assignment->route_id)
            ->orderBy('name')
            ->get();

        $route = $assignment->route;
        if ($request->filled('route_id')) {
            $route = $routes->firstWhere('id', (int) $request->query('route_id')) ?: $route;
        }

        return view('schedule.edit', compact('assignment', 'drivers', 'vehicles', 'students', 'routes', 'route'));
    }

    public function update(Request $request, DailyAssignment $assignment)
    {
        $validated = $this->validateAssignment($request);
        $this->validateAssignmentDetails($validated, $request);
        $this->ensureResourcesAvailable($validated, $assignment->id);

        DB::transaction(function () use ($validated, $assignment, $request) {
            $passengerIds = collect($request->input('passenger_ids', []))->filter()->unique()->values();

            $assignment->update([
                'date'                     => $validated['date'],
                'route_id'                 => $validated['route_id'],
                'driver_id'                => $validated['driver_id'],
                'vehicle_id'               => $validated['vehicle_id'],
                'estimated_passengers'     => $passengerIds->count(),
                'estimated_departure_time' => $validated['estimated_departure_time'] ?? null,
                'notes'                    => $validated['notes'] ?? null,
                'status'                   => $validated['status'],
            ]);

            $this->syncStops($assignment, $request);
            $this->syncPassengers($assignment, $request);
        });

        return redirect()->route('schedule.index', ['date' => $assignment->date->toDateString()])
            ->with('success', 'Schedule updated successfully.');
    }

    public function destroy(DailyAssignment $assignment)
    {
        $date = $assignment->date->toDateString();
        $assignment->delete();

        return redirect()->route('schedule.index', ['date' => $date])
            ->with('success', 'Schedule removed.');
    }

    private function resolveDate($raw): \Carbon\Carbon
    {
        try {
            return (is_string($raw) && $raw !== '') ? \Carbon\Carbon::parse($raw)->startOfDay() : today();
        } catch (\Throwable) {
            return today();
        }
    }

    private function validateAssignment(Request $request): array
    {
        return $request->validate([
            'date'                     => 'required|date',
            'route_id'                 => 'required|exists:routes,id',
            'driver_id'                => 'required|exists:drivers,id',
            'vehicle_id'               => 'required|exists:vehicles,id',
            'estimated_departure_time' => 'nullable|string|max:50',
            'notes'                    => 'nullable|string|max:1000',
            'status'                   => 'required|string|in:Scheduled,In Progress,Completed,Cancelled',
            'selected_stop_ids'        => 'required|array|min:1',
            'selected_stop_ids.*'      => 'integer|exists:route_stops,id',
            'stop_pickup_time'         => 'nullable|array',
            'stop_pickup_time.*'       => 'nullable|string|max:50',
            'stop_dropoff_time'        => 'nullable|array',
            'stop_dropoff_time.*'      => 'nullable|string|max:50',
            'passenger_ids'            => 'nullable|array',
            'passenger_ids.*'          => 'integer|exists:passengers,id',
            'student_stop'             => 'nullable|array',
            'student_stop.*'           => 'nullable|integer|exists:route_stops,id',
            'student_pickup_time'      => 'nullable|array',
            'student_pickup_time.*'    => 'nullable|string|max:50',
            'student_dropoff_time'     => 'nullable|array',
            'student_dropoff_time.*'   => 'nullable|string|max:50',
        ]);
    }

    private function validateAssignmentDetails(array $validated, Request $request): void
    {
        $route = Route::with('routeStops')->findOrFail($validated['route_id']);
        $routeStopIds = $route->routeStops->pluck('id')->map(fn ($id) => (int) $id);
        $selectedStopIds = collect($request->input('selected_stop_ids', []))->map(fn ($id) => (int) $id);

        if ($selectedStopIds->diff($routeStopIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'selected_stop_ids' => 'Every selected stop must belong to the selected route.',
            ]);
        }

        $passengerIds = collect($request->input('passenger_ids', []))->map(fn ($id) => (int) $id)->unique();
        if ($passengerIds->isNotEmpty()) {
            $validPassengerCount = Passenger::whereIn('id', $passengerIds)
                ->where('approval_status', 'approved')
                ->where('status', 'Active')
                ->count();

            if ($validPassengerCount !== $passengerIds->count()) {
                throw ValidationException::withMessages([
                    'passenger_ids' => 'Only active, approved passengers can be added to a schedule.',
                ]);
            }
        }

        foreach ($passengerIds as $passengerId) {
            $stopId = $request->input("student_stop.$passengerId");
            if ($stopId && !$selectedStopIds->contains((int) $stopId)) {
                throw ValidationException::withMessages([
                    "student_stop.$passengerId" => 'A student stop must be one of the stops selected for this assignment.',
                ]);
            }
        }
    }

    private function ensureResourcesAvailable(array $validated, ?int $ignoreAssignmentId = null): void
    {
        $routeConflict = DailyAssignment::whereDate('date', $validated['date'])
            ->where('route_id', $validated['route_id'])
            ->when($ignoreAssignmentId, fn ($q) => $q->where('id', '!=', $ignoreAssignmentId))
            ->exists();

        if ($routeConflict) {
            throw ValidationException::withMessages([
                'date' => 'This route already has another assignment on the selected date.',
            ]);
        }

        if (($validated['status'] ?? 'Scheduled') === 'Cancelled') {
            return;
        }

        $driverConflict = DailyAssignment::whereDate('date', $validated['date'])
            ->where('driver_id', $validated['driver_id'])
            ->where('status', '!=', 'Cancelled')
            ->when($ignoreAssignmentId, fn ($q) => $q->where('id', '!=', $ignoreAssignmentId))
            ->exists();

        if ($driverConflict) {
            throw ValidationException::withMessages([
                'driver_id' => 'This driver already has another assignment on the selected date.',
            ]);
        }

        $vehicleConflict = DailyAssignment::whereDate('date', $validated['date'])
            ->where('vehicle_id', $validated['vehicle_id'])
            ->where('status', '!=', 'Cancelled')
            ->when($ignoreAssignmentId, fn ($q) => $q->where('id', '!=', $ignoreAssignmentId))
            ->exists();

        if ($vehicleConflict) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'This bus/vehicle already has another assignment on the selected date.',
            ]);
        }
    }

    private function syncStops(DailyAssignment $assignment, Request $request): void
    {
        $assignment->stops()->delete();

        foreach (collect($request->input('selected_stop_ids', []))->unique() as $stopId) {
            $pickup = trim((string) $request->input("stop_pickup_time.$stopId", ''));
            $dropoff = trim((string) $request->input("stop_dropoff_time.$stopId", ''));

            DailyAssignmentStop::create([
                'daily_assignment_id' => $assignment->id,
                'route_stop_id'       => $stopId,
                'estimated_time'      => $pickup !== '' ? $pickup : null,
                'pickup_time'         => $pickup !== '' ? $pickup : null,
                'dropoff_time'        => $dropoff !== '' ? $dropoff : null,
            ]);
        }
    }

    private function syncPassengers(DailyAssignment $assignment, Request $request): void
    {
        $assignment->passengerAssignments()->delete();

        foreach (collect($request->input('passenger_ids', []))->filter()->unique() as $passengerId) {
            $stopId = $request->input("student_stop.$passengerId");
            $pickup = trim((string) $request->input("student_pickup_time.$passengerId", ''));
            $dropoff = trim((string) $request->input("student_dropoff_time.$passengerId", ''));

            DailyAssignmentPassenger::create([
                'daily_assignment_id' => $assignment->id,
                'passenger_id'        => $passengerId,
                'route_stop_id'       => $stopId ?: null,
                'pickup_time'         => $pickup !== '' ? $pickup : null,
                'dropoff_time'        => $dropoff !== '' ? $dropoff : null,
            ]);
        }
    }
}
