<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    /** Dedicated scanner terminal. */
    public function index()
    {
        $today = today()->toDateString();

        $stats = [
            'present'  => Attendance::whereDate('date', $today)->where('status', 'Present')->count(),
            'qr_scans' => Attendance::whereDate('date', $today)->where('method', 'qr')->count(),
        ];

        return view('dashboard.scanner', compact('stats'));
    }

    /**
     * Resolve a typed bus number to operational details. The persistent
     * Schedule takes priority; the permanent bus/route relationship remains
     * the fallback only when no Schedule has been configured for that route.
     */
    public function busDetails(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bus_number' => ['required', 'string', 'max:50'],
        ]);

        $typedNumber = trim($validated['bus_number']);

        $vehicle = Vehicle::with(['driver', 'route.Stops'])
            ->whereRaw('LOWER(number) = ?', [strtolower($typedNumber)])
            ->first();

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Bus not found. Check the bus number and try again.',
            ], 404);
        }

        if ($vehicle->status !== 'Active') {
            return response()->json([
                'success' => false,
                'message' => "Bus {$vehicle->number} is currently {$vehicle->status} and cannot start attendance.",
            ], 422);
        }

        $scheduleQuery = Schedule::current()->with([
            'driver',
            'vehicle',
            'route.Stops',
            'stops',
        ]);

        // First preference: this exact bus is explicitly assigned in the Schedule.
        $schedule = (clone $scheduleQuery)
            ->where('vehicle_id', $vehicle->id)
            ->first();

        // Otherwise resolve the persistent Schedule for the bus's normal route.
        if (!$schedule && $vehicle->route) {
            $routeSchedule = (clone $scheduleQuery)
                ->where('route_id', $vehicle->route->id)
                ->first();

            if ($routeSchedule && $routeSchedule->vehicle_id && $routeSchedule->vehicle_id !== $vehicle->id) {
                $replacement = $routeSchedule->vehicle?->number ?: 'another bus';

                return response()->json([
                    'success' => false,
                    'message' => "Bus {$vehicle->number} is not assigned to this route in the active Schedule. The assigned vehicle is {$replacement}.",
                ], 422);
            }

            $schedule = $routeSchedule;
        }

        if ($schedule?->status === 'Cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'This route is currently marked Cancelled in the active Schedule.',
            ], 422);
        }

        $route = $schedule?->route ?: $vehicle->route;
        $driver = $schedule?->driver ?: $vehicle->driver;

        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => "Bus {$vehicle->number} has no active route assigned.",
            ], 422);
        }

        if ($schedule) {
            $stops = $schedule->stopsWithTimes()->map(function (array $item) {
                return [
                    'id'       => $item['stop']->id,
                    'name'     => $item['stop']->name,
                    'sequence' => $item['stop']->sequence,
                    'time'     => $item['time'],
                ];
            })->values();
        } else {
            $stops = $route->Stops->map(fn ($stop) => [
                'id'       => $stop->id,
                'name'     => $stop->name,
                'sequence' => $stop->sequence,
                'time'     => $stop->eta,
            ])->values();
        }

        return response()->json([
            'success' => true,
            'message' => 'Bus found.',
            'bus' => [
                'id'       => $vehicle->id,
                'number'   => $vehicle->number,
                'type'     => $vehicle->type,
                'capacity' => $vehicle->capacity,
                'status'   => $vehicle->status,
            ],
            'driver' => $driver ? [
                'id'    => $driver->id,
                'name'  => $driver->name,
                'phone' => $driver->phone,
            ] : null,
            'route' => [
                'id'   => $route->id,
                'name' => $route->name,
                'from' => $route->from,
                'to'   => $route->to,
            ],
            'stops' => $stops,
            'assignment' => [
                'schedule_specific' => (bool) $schedule,
                'status'         => $schedule?->status,
                'departure_time' => $schedule?->estimated_departure_time,
            ],
        ]);
    }
}
