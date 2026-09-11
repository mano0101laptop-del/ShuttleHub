<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DailyAssignment;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    /**
     * Dedicated scanner terminal. A scanner account first selects the bus,
     * confirms today's driver / route / stops, and then opens the camera.
     */
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
     * Resolve a typed bus number to the operational details the scanner needs.
     * Today's Schedule takes priority, while the permanent bus/route
     * relationship remains a safe fallback when no day-specific assignment
     * has been created yet.
     */
    public function busDetails(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bus_number' => ['required', 'string', 'max:50'],
        ]);

        $typedNumber = trim($validated['bus_number']);

        $vehicle = Vehicle::with(['driver', 'route.routeStops'])
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

        $assignmentQuery = DailyAssignment::with([
                'driver',
                'vehicle',
                'route.routeStops',
                'stops',
            ])
            ->whereDate('date', today())
            ->where('status', '!=', 'Cancelled');

        // First preference: this exact bus was explicitly assigned today.
        $assignment = (clone $assignmentQuery)
            ->where('vehicle_id', $vehicle->id)
            ->first();

        // Otherwise use today's assignment for the bus's normal route only
        // when that assignment did not swap in a different vehicle.
        if (!$assignment && $vehicle->route) {
            $routeAssignment = (clone $assignmentQuery)
                ->where('route_id', $vehicle->route->id)
                ->first();

            if ($routeAssignment && $routeAssignment->vehicle_id && $routeAssignment->vehicle_id !== $vehicle->id) {
                $replacement = $routeAssignment->vehicle?->number ?: 'another bus';

                return response()->json([
                    'success' => false,
                    'message' => "Bus {$vehicle->number} is not running its normal route today. Today's assigned vehicle is {$replacement}.",
                ], 422);
            }

            $assignment = $routeAssignment;
        }

        $route = $assignment?->route ?: $vehicle->route;
        $driver = $assignment?->driver ?: $vehicle->driver;

        if (!$route) {
            return response()->json([
                'success' => false,
                'message' => "Bus {$vehicle->number} has no active route assigned.",
            ], 422);
        }

        if ($assignment) {
            $stops = $assignment->stopsWithTimes()->map(function (array $item) {
                return [
                    'id'       => $item['stop']->id,
                    'name'     => $item['stop']->name,
                    'sequence' => $item['stop']->sequence,
                    'time'     => $item['time'],
                ];
            })->values();
        } else {
            $stops = $route->routeStops->map(fn ($stop) => [
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
                'today_specific' => (bool) $assignment,
                'status'         => $assignment?->status,
                'departure_time' => $assignment?->estimated_departure_time,
            ],
        ]);
    }
}
