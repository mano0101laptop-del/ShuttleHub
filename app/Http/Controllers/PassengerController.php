<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PassengerController extends Controller
{
    public function index()
    {
        $pendingPassengers = Passenger::with(['route', 'routeStop', 'assignedDriver', 'assignedVehicle', 'user'])
            ->where('approval_status', 'pending')
            ->latest()
            ->get();

        $cancellationRequests = Passenger::with(['route', 'routeStop', 'user'])
            ->where('cancellation_status', 'requested')
            ->latest('cancellation_requested_at')
            ->get();

        $passengers = Passenger::with(['route', 'routeStop', 'assignedDriver', 'assignedVehicle', 'user'])
            ->whereIn('approval_status', ['approved', 'rejected'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('passengers.index', compact('passengers', 'pendingPassengers', 'cancellationRequests'));
    }

    public function create()
    {
        return view('passengers.create', $this->transportFormData());
    }

    public function store(Request $request)
    {
        $validated = $this->validatePassenger($request);
        $transport = $this->resolveTransportFields($validated);

        $passenger = Passenger::create(array_merge([
            'name'                 => $validated['name'],
            'roll'                 => $validated['roll'],
            'department'           => $validated['department'],
            'status'               => $validated['status'],
            'approval_status'      => 'approved',
            'qr_token'             => Passenger::generateQrToken(),
            'fingerprint_hash'     => Hash::make($validated['fingerprint_data']),
            'fingerprint_enrolled' => true,
        ], $transport));

        return redirect()->route('passengers.show', $passenger)
            ->with('success', 'Passenger registered and transport assignment saved. QR Code has been issued.');
    }

    public function show(Passenger $passenger)
    {
        $passenger->load([
            'route.vehicle.driver', 'route.routeStops', 'routeStop',
            'assignedDriver', 'assignedVehicle', 'attendances',
        ]);
        $totalDays     = $passenger->attendances()->count();
        $presentDays   = $passenger->attendances()->where('status', 'Present')->count();
        $attendancePct = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

        return view('passengers.show', compact('passenger', 'totalDays', 'presentDays', 'attendancePct'));
    }

    public function edit(Passenger $passenger)
    {
        $passenger->load(['routeStop', 'assignedDriver', 'assignedVehicle']);
        return view('passengers.edit', array_merge(['passenger' => $passenger], $this->transportFormData($passenger)));
    }

    public function update(Request $request, Passenger $passenger)
    {
        $validated = $this->validatePassenger($request, $passenger);
        $transport = $this->resolveTransportFields($validated);

        $data = array_merge([
            'name'       => $validated['name'],
            'roll'       => $validated['roll'],
            'department' => $validated['department'],
            'status'     => $validated['status'],
        ], $transport);

        if (!empty($validated['fingerprint_data'])) {
            $data['fingerprint_hash']     = Hash::make($validated['fingerprint_data']);
            $data['fingerprint_enrolled'] = true;
        }

        $passenger->update($data);

        if ($passenger->user) {
            $passenger->user->update(['name' => $validated['name']]);
        }

        return redirect()->route('passengers.index')->with('success', 'Passenger and transport assignment updated successfully!');
    }

    public function destroy(Passenger $passenger)
    {
        $user = $passenger->user;
        $passenger->delete();

        if ($user) {
            $user->delete();
        }

        return redirect()->route('passengers.index')->with('success', 'Passenger and linked login removed successfully!');
    }

    public function approve(Passenger $passenger)
    {
        $passenger->update([
            'approval_status' => 'approved',
            'status'          => 'Active',
        ]);

        return redirect()->back()->with('success', "{$passenger->name}'s transport application has been approved.");
    }

    public function reject(Passenger $passenger)
    {
        $passenger->update([
            'approval_status' => 'rejected',
            'status'          => 'Inactive',
        ]);

        return redirect()->back()->with('success', "{$passenger->name}'s transport application has been rejected.");
    }

    public function regenerateQr(Passenger $passenger)
    {
        $passenger->update(['qr_token' => Passenger::generateQrToken()]);

        return redirect()->back()->with('success',
            "{$passenger->name}'s old card has been invalidated. Please print and issue their new QR card.");
    }

    public function downloadQr(Passenger $passenger)
    {
        if (!$passenger->qrIsActive()) {
            return redirect()->route('passengers.show', $passenger)
                ->with('error', 'Your QR pass unlocks once your application is approved and the transport fee is paid & approved.');
        }

        return view('passengers.qr-download', compact('passenger'));
    }

    public function requestCancellation(Request $request, Passenger $passenger)
    {
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        $passenger->update([
            'cancellation_status'       => 'requested',
            'cancellation_reason'       => $request->cancellation_reason,
            'cancellation_requested_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Your cancellation request has been submitted to the admin.');
    }

    public function approveCancellation(Passenger $passenger)
    {
        $passenger->update([
            'cancellation_status' => 'approved',
            'status'              => 'Inactive',
        ]);

        return redirect()->back()->with('success', "{$passenger->name}'s transport subscription has been cancelled.");
    }

    public function rejectCancellation(Passenger $passenger)
    {
        $passenger->update([
            'cancellation_status' => 'rejected',
        ]);

        return redirect()->back()->with('success', "{$passenger->name}'s cancellation request has been rejected.");
    }

    private function transportFormData(?Passenger $passenger = null): array
    {
        $routes = Route::with(['routeStops', 'vehicle.driver'])
            ->where(function ($q) use ($passenger) {
                $q->where('status', 'Active');
                if ($passenger?->route_id) {
                    $q->orWhere('id', $passenger->route_id);
                }
            })
            ->orderBy('name')
            ->get();

        $drivers = Driver::where(function ($q) use ($passenger) {
                $q->where('status', 'Active');
                if ($passenger?->driver_id) {
                    $q->orWhere('id', $passenger->driver_id);
                }
            })
            ->orderBy('name')
            ->get();

        $vehicles = Vehicle::where(function ($q) use ($passenger) {
                $q->where('status', 'Active');
                if ($passenger?->vehicle_id) {
                    $q->orWhere('id', $passenger->vehicle_id);
                }
            })
            ->orderBy('number')
            ->get();

        return compact('routes', 'drivers', 'vehicles');
    }

    private function validatePassenger(Request $request, ?Passenger $passenger = null): array
    {
        $id = $passenger?->id;

        return $request->validate([
            'name'             => 'required|string|max:255',
            'roll'             => 'required|string|unique:passengers,roll' . ($id ? ',' . $id : ''),
            'department'       => 'required|string|max:255',
            'status'           => 'required|string|in:Active,Inactive',
            'route_id'         => 'nullable|exists:routes,id',
            'route_stop_id'    => 'nullable|exists:route_stops,id',
            'driver_id'        => 'nullable|exists:drivers,id',
            'vehicle_id'       => 'nullable|exists:vehicles,id',
            'pickup_time'      => 'nullable|string|max:50',
            'dropoff_time'     => 'nullable|string|max:50',
            'fingerprint_data' => $passenger ? 'nullable|string|min:6|max:20' : 'required|string|min:6|max:20',
        ]);
    }

    private function resolveTransportFields(array $validated): array
    {
        $routeId = $validated['route_id'] ?? null;
        $stopId = $validated['route_stop_id'] ?? null;
        $stopName = null;

        if ($stopId) {
            $stop = RouteStop::findOrFail($stopId);
            if (!$routeId || (int) $stop->route_id !== (int) $routeId) {
                throw ValidationException::withMessages([
                    'route_stop_id' => 'The selected stop must belong to the selected route.',
                ]);
            }
            $stopName = $stop->name;
        }

        return [
            'route_id'      => $routeId,
            'route_stop_id' => $stopId,
            'stop'          => $stopName,
            'driver_id'     => $validated['driver_id'] ?? null,
            'vehicle_id'    => $validated['vehicle_id'] ?? null,
            'pickup_time'   => $validated['pickup_time'] ?? null,
            'dropoff_time'  => $validated['dropoff_time'] ?? null,
        ];
    }
}
