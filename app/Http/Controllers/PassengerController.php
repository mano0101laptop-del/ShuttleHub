<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\Stop;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PassengerController extends Controller
{
    public function index()
    {
        // Administrative approval queues are intentionally not exposed to Incharge.
        $pendingPassengers = collect();
        $cancellationRequests = collect();

        if (auth()->user()->role === 'admin') {
            $pendingPassengers = Passenger::with(['route', 'Stop', 'assignedDriver', 'assignedVehicle', 'user'])
                ->where('approval_status', 'pending')
                ->latest()
                ->get();

            $cancellationRequests = Passenger::with(['route', 'Stop', 'user'])
                ->where('cancellation_status', 'requested')
                ->latest('cancellation_requested_at')
                ->get();
        }

        $passengers = Passenger::with(['route', 'Stop', 'assignedDriver', 'assignedVehicle', 'user'])
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

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('passengers/photos', 'public')
            : null;

        $passenger = Passenger::create(array_merge([
            'name'               => $validated['name'],
            'roll'               => $validated['roll'],
            'contact_number'     => $validated['contact_number'],
            'passenger_type'     => $validated['passenger_type'],
            'photo_path'         => $photoPath,
            'department'         => $validated['department'] ?? null,
            'address'            => $validated['address'],
            'emergency_contact'  => $validated['emergency_contact'],
            'status'             => $validated['status'],
            'approval_status'    => 'approved',
        ], $transport));

        return redirect()->route('passengers.show', $passenger)
            ->with('success', 'Passenger registered and transport assignment saved.');
    }

    public function show(Passenger $passenger)
    {
        $passenger->load([
            'route.vehicle.driver', 'route.Stops', 'Stop',
            'assignedDriver', 'assignedVehicle',
        ]);

        return view('passengers.show', compact('passenger'));
    }

    public function edit(Passenger $passenger)
    {
        $passenger->load(['Stop', 'assignedDriver', 'assignedVehicle']);
        return view('passengers.edit', array_merge(['passenger' => $passenger], $this->transportFormData($passenger)));
    }

    public function update(Request $request, Passenger $passenger)
    {
        $validated = $this->validatePassenger($request, $passenger);
        $transport = $this->resolveTransportFields($validated);

        $data = array_merge([
            'name'               => $validated['name'],
            'roll'               => $validated['roll'],
            'contact_number'     => $validated['contact_number'],
            'passenger_type'     => $validated['passenger_type'],
            'department'         => $validated['department'] ?? null,
            'address'            => $validated['address'],
            'emergency_contact'  => $validated['emergency_contact'],
            'status'             => $validated['status'],
        ], $transport);

        if ($request->hasFile('photo')) {
            $newPhotoPath = $request->file('photo')->store('passengers/photos', 'public');

            if ($passenger->photo_path) {
                Storage::disk('public')->delete($passenger->photo_path);
            }

            $data['photo_path'] = $newPhotoPath;
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

        if ($passenger->photo_path) {
            Storage::disk('public')->delete($passenger->photo_path);
        }

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

    /**
     * Passenger self-service profile photo used on the dashboard and transport card.
     */
    public function updateOwnPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], [
            'photo.required' => 'Please choose a profile photo to upload.',
            'photo.image'    => 'The selected file must be an image.',
            'photo.mimes'    => 'Only JPG, JPEG, PNG, or WEBP images are allowed.',
            'photo.max'      => 'The profile photo must be smaller than 4MB.',
        ]);

        $passenger = Passenger::where('user_id', auth()->id())->firstOrFail();
        $newPhotoPath = $request->file('photo')->store('passengers/photos', 'public');

        if ($passenger->photo_path) {
            Storage::disk('public')->delete($passenger->photo_path);
        }

        $passenger->update([
            'photo_path' => $newPhotoPath,
        ]);

        return redirect()->back()->with('success', 'Your profile photo has been updated and will now appear on your transport card.');
    }

    /**
     * Printable/downloadable two-sided digital transport card.
     */
    public function transportCard(Passenger $passenger)
    {
        abort_unless($passenger->isApproved(), 403, 'The transport card is available after the passenger is approved.');

        $passenger->load([
            'route.vehicle.driver', 'route.Stops', 'Stop',
            'assignedDriver', 'assignedVehicle',
        ]);

        $cardId = 'SH-' . str_pad((string) $passenger->id, 6, '0', STR_PAD_LEFT);
        $feePayment = $passenger->activeFeePayment();
        $photoDataUri = $this->photoDataUri($passenger->photo_path);

        return view('passengers.transport-card', compact(
            'passenger', 'cardId', 'feePayment', 'photoDataUri'
        ));
    }

    private function transportFormData(?Passenger $passenger = null): array
    {
        $routes = Route::with(['Stops', 'vehicle.driver'])
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
            'name'               => 'required|string|max:255',
            'roll'               => 'required|string|unique:passengers,roll' . ($id ? ',' . $id : ''),
            'contact_number'     => 'required|string|max:20',
            'passenger_type'     => 'required|string|in:Student,Teacher,Staff',
            'photo'              => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'department'         => 'nullable|string|max:255',
            'address'            => 'required|string|max:500',
            'emergency_contact'  => 'required|string|max:20',
            'status'             => 'required|string|in:Active,Inactive',
            'route_id'           => 'nullable|exists:routes,id',
            'stop_id'            => 'nullable|exists:stops,id',
            'driver_id'          => 'nullable|exists:drivers,id',
            'vehicle_id'         => 'nullable|exists:vehicles,id',
            'pickup_time'        => 'nullable|string|max:50',
            'dropoff_time'       => 'nullable|string|max:50',
        ]);
    }

    private function photoDataUri(?string $path): ?string
    {
        if (!$path || !Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';
        $contents = Storage::disk('public')->get($path);

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }

    private function resolveTransportFields(array $validated): array
    {
        $routeId = $validated['route_id'] ?? null;
        $stopId = $validated['stop_id'] ?? null;
        $stopName = null;

        if ($stopId) {
            $stop = Stop::findOrFail($stopId);
            if (!$routeId || (int) $stop->route_id !== (int) $routeId) {
                throw ValidationException::withMessages([
                    'stop_id' => 'The selected stop must belong to the selected route.',
                ]);
            }
            $stopName = $stop->name;
        }

        return [
            'route_id'      => $routeId,
            'stop_id' => $stopId,
            'stop'          => $stopName,
            'driver_id'     => $validated['driver_id'] ?? null,
            'vehicle_id'    => $validated['vehicle_id'] ?? null,
            'pickup_time'   => $validated['pickup_time'] ?? null,
            'dropoff_time'  => $validated['dropoff_time'] ?? null,
        ];
    }
}
