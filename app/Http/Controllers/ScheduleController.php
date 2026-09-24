<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\SchedulePassenger;
use App\Models\ScheduleStop;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::current()
            ->with([
                'route.Stops', 'driver', 'vehicle',
                'stops.Stop', 'passengerAssignments.passenger',
            ])
            ->get()
            ->sortBy(fn ($schedule) => $schedule->route->name ?? '');

        $unassignedRoutes = Route::with('vehicle.driver')
            ->where('status', 'Active')
            ->whereNotIn('id', $schedules->pluck('route_id'))
            ->orderBy('name')
            ->get();

        return view('schedule.index', compact('schedules', 'unassignedRoutes'));
    }

    public function create(Request $request)
    {
        $scheduledRouteIds = Schedule::current()->pluck('route_id');
        $routes = Route::with('Stops')
            ->where('status', 'Active')
            ->whereNotIn('id', $scheduledRouteIds)
            ->orderBy('name')
            ->get();

        $selectedRoute = null;
        if ($request->filled('route_id')) {
            $selectedRoute = Route::with(['Stops', 'vehicle.driver'])
                ->where('status', 'Active')
                ->whereNotIn('id', $scheduledRouteIds)
                ->find($request->query('route_id'));
        }

        $drivers = Driver::where('status', 'Active')->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'Active')->orderBy('number')->get();
        $passengers = Passenger::with(['route', 'Stop'])
            ->where('approval_status', 'approved')
            ->where('status', 'Active')
            ->orderBy('name')
            ->get();

        return view('schedule.create', compact(
            'routes', 'selectedRoute', 'drivers', 'vehicles', 'passengers'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSchedule($request);
        $this->validateScheduleDetails($validated, $request);
        $this->ensureResourcesAvailable($validated);

        $schedule = DB::transaction(function () use ($validated, $request) {
            $passengerIds = collect($request->input('passenger_ids', []))->filter()->unique()->values();

            $schedule = Schedule::create([
                // Legacy table compatibility only. This value is never used to
                // rotate, reset, select, or replace the persistent Schedule.
                'date'                     => today()->toDateString(),
                'route_id'                 => $validated['route_id'],
                'driver_id'                => $validated['driver_id'],
                'vehicle_id'               => $validated['vehicle_id'],
                'estimated_passengers'     => $passengerIds->count(),
                'estimated_departure_time' => $validated['estimated_departure_time'] ?? null,
                'notes'                    => $validated['notes'] ?? null,
                'status'                   => $validated['status'],
                'created_by'               => Auth::id(),
            ]);

            $this->syncStops($schedule, $request);
            $this->syncPassengers($schedule, $request);

            return $schedule->fresh();
        });

        return redirect()->route('schedule.index')
            ->with('success', 'Schedule saved with driver, bus, stops, passengers and timings. It will remain active until manually updated.');
    }

    public function edit(Request $request, Schedule $schedule)
    {
        abort_unless(Schedule::current()->whereKey($schedule->id)->exists(), 404);

        $schedule->load([
            'route.Stops', 'stops.Stop',
            'passengerAssignments.passenger.route', 'passengerAssignments.Stop',
        ]);

        $drivers = Driver::where('status', 'Active')
            ->orWhere('id', $schedule->driver_id)
            ->orderBy('name')->get();
        $vehicles = Vehicle::where('status', 'Active')
            ->orWhere('id', $schedule->vehicle_id)
            ->orderBy('number')->get();
        $currentPassengerIds = $schedule->passengerAssignments->pluck('passenger_id');
        $passengers = Passenger::with(['route', 'Stop'])
            ->where('approval_status', 'approved')
            ->where(function ($query) use ($currentPassengerIds) {
                $query->where('status', 'Active')->orWhereIn('id', $currentPassengerIds);
            })
            ->orderBy('name')
            ->get();

        $otherScheduledRouteIds = Schedule::current()
            ->where('id', '!=', $schedule->id)
            ->pluck('route_id');

        $routes = Route::with(['Stops', 'vehicle.driver'])
            ->where(function ($query) use ($schedule) {
                $query->where('status', 'Active')->orWhere('id', $schedule->route_id);
            })
            ->whereNotIn('id', $otherScheduledRouteIds)
            ->orderBy('name')
            ->get();

        $route = $schedule->route;
        if ($request->filled('route_id')) {
            $route = $routes->firstWhere('id', (int) $request->query('route_id')) ?: $route;
        }

        return view('schedule.edit', compact('schedule', 'drivers', 'vehicles', 'passengers', 'routes', 'route'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        abort_unless(Schedule::current()->whereKey($schedule->id)->exists(), 404);

        $validated = $this->validateSchedule($request);
        $this->validateScheduleDetails($validated, $request);
        $this->ensureResourcesAvailable($validated, $schedule->id);

        DB::transaction(function () use ($validated, $schedule, $request) {
            $passengerIds = collect($request->input('passenger_ids', []))->filter()->unique()->values();
            $previousRouteId = $schedule->route_id;

            $schedule->update([
                // Intentionally do not touch the legacy `date` column. The
                // Schedule changes only through this explicit user update.
                'route_id'                 => $validated['route_id'],
                'driver_id'                => $validated['driver_id'],
                'vehicle_id'               => $validated['vehicle_id'],
                'estimated_passengers'     => $passengerIds->count(),
                'estimated_departure_time' => $validated['estimated_departure_time'] ?? null,
                'notes'                    => $validated['notes'] ?? null,
                'status'                   => $validated['status'],
            ]);

            $this->syncStops($schedule, $request);
            $this->syncPassengers($schedule, $request);

            // If an existing Schedule is moved to another route, remove only
            // obsolete legacy rows for its former route so an old date-based
            // assignment cannot become active again by accident.
            if ((int) $previousRouteId !== (int) $schedule->route_id) {
                Schedule::where('route_id', $previousRouteId)
                    ->where('id', '!=', $schedule->id)
                    ->delete();
            }
        });

        return redirect()->route('schedule.index')
            ->with('success', 'Schedule updated successfully.');
    }

    public function destroy(Schedule $schedule)
    {
        abort_unless(Schedule::current()->whereKey($schedule->id)->exists(), 404);

        $routeId = $schedule->route_id;

        // Remove legacy date-based rows for this route too, otherwise deleting
        // the current Schedule could reactivate an older historical row.
        Schedule::where('route_id', $routeId)->delete();

        return redirect()->route('schedule.index')
            ->with('success', 'Schedule removed.');
    }

    private function validateSchedule(Request $request): array
    {
        return $request->validate([
            'route_id'                    => 'required|exists:routes,id',
            'driver_id'                   => 'required|exists:drivers,id',
            'vehicle_id'                  => 'required|exists:vehicles,id',
            'estimated_departure_time'    => 'nullable|string|max:50',
            'notes'                       => 'nullable|string|max:1000',
            'status'                      => 'required|string|in:Scheduled,In Progress,Completed,Cancelled',
            'selected_stop_ids'           => 'required|array|min:1',
            'selected_stop_ids.*'         => 'integer|exists:stops,id',
            'stop_pickup_time'            => 'nullable|array',
            'stop_pickup_time.*'          => 'nullable|string|max:50',
            'stop_dropoff_time'           => 'nullable|array',
            'stop_dropoff_time.*'         => 'nullable|string|max:50',
            'passenger_ids'               => 'nullable|array',
            'passenger_ids.*'             => 'integer|exists:passengers,id',
            'passenger_stop'              => 'nullable|array',
            'passenger_stop.*'            => 'nullable|integer|exists:stops,id',
            'passenger_pickup_time'       => 'nullable|array',
            'passenger_pickup_time.*'     => 'nullable|string|max:50',
            'passenger_dropoff_time'      => 'nullable|array',
            'passenger_dropoff_time.*'    => 'nullable|string|max:50',
        ]);
    }

    private function validateScheduleDetails(array $validated, Request $request): void
    {
        $route = Route::with('Stops')->findOrFail($validated['route_id']);
        $StopIds = $route->Stops->pluck('id')->map(fn ($id) => (int) $id);
        $selectedStopIds = collect($request->input('selected_stop_ids', []))->map(fn ($id) => (int) $id);

        if ($selectedStopIds->diff($StopIds)->isNotEmpty()) {
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
                    'passenger_ids' => 'Only active, approved passengers can be added to a Schedule.',
                ]);
            }
        }

        foreach ($passengerIds as $passengerId) {
            $stopId = $request->input("passenger_stop.$passengerId");
            if ($stopId && !$selectedStopIds->contains((int) $stopId)) {
                throw ValidationException::withMessages([
                    "passenger_stop.$passengerId" => 'A passenger stop must be one of the stops selected for this Schedule.',
                ]);
            }
        }
    }

    private function ensureResourcesAvailable(array $validated, ?int $ignoreScheduleId = null): void
    {
        $currentSchedules = Schedule::current();

        $routeConflict = (clone $currentSchedules)
            ->where('route_id', $validated['route_id'])
            ->when($ignoreScheduleId, fn ($query) => $query->where('id', '!=', $ignoreScheduleId))
            ->exists();

        if ($routeConflict) {
            throw ValidationException::withMessages([
                'route_id' => 'This route already has a Schedule. Edit the existing Schedule instead.',
            ]);
        }

        if (($validated['status'] ?? 'Scheduled') === 'Cancelled') {
            return;
        }

        $driverConflict = (clone $currentSchedules)
            ->where('driver_id', $validated['driver_id'])
            ->where('status', '!=', 'Cancelled')
            ->when($ignoreScheduleId, fn ($query) => $query->where('id', '!=', $ignoreScheduleId))
            ->exists();

        if ($driverConflict) {
            throw ValidationException::withMessages([
                'driver_id' => 'This driver is already assigned to another active Schedule.',
            ]);
        }

        $vehicleConflict = (clone $currentSchedules)
            ->where('vehicle_id', $validated['vehicle_id'])
            ->where('status', '!=', 'Cancelled')
            ->when($ignoreScheduleId, fn ($query) => $query->where('id', '!=', $ignoreScheduleId))
            ->exists();

        if ($vehicleConflict) {
            throw ValidationException::withMessages([
                'vehicle_id' => 'This bus/vehicle is already assigned to another active Schedule.',
            ]);
        }
    }

    private function syncStops(Schedule $schedule, Request $request): void
    {
        $schedule->stops()->delete();

        foreach (collect($request->input('selected_stop_ids', []))->unique() as $stopId) {
            $pickup = trim((string) $request->input("stop_pickup_time.$stopId", ''));
            $dropoff = trim((string) $request->input("stop_dropoff_time.$stopId", ''));

            ScheduleStop::create([
                'schedule_id' => $schedule->id,
                'stop_id'       => $stopId,
                'estimated_time'      => $pickup !== '' ? $pickup : null,
                'pickup_time'         => $pickup !== '' ? $pickup : null,
                'dropoff_time'        => $dropoff !== '' ? $dropoff : null,
            ]);
        }
    }

    private function syncPassengers(Schedule $schedule, Request $request): void
    {
        $schedule->passengerAssignments()->delete();

        foreach (collect($request->input('passenger_ids', []))->filter()->unique() as $passengerId) {
            $stopId = $request->input("passenger_stop.$passengerId");
            $pickup = trim((string) $request->input("passenger_pickup_time.$passengerId", ''));
            $dropoff = trim((string) $request->input("passenger_dropoff_time.$passengerId", ''));

            SchedulePassenger::create([
                'schedule_id' => $schedule->id,
                'passenger_id'        => $passengerId,
                'stop_id'       => $stopId ?: null,
                'pickup_time'         => $pickup !== '' ? $pickup : null,
                'dropoff_time'        => $dropoff !== '' ? $dropoff : null,
            ]);
        }
    }
}
