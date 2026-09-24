<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Driver;
use App\Models\Passenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    /**
     * Passenger self-service form. Admin never creates complaints — Admin
     * only receives and manages the ones passengers submit (see index()).
     */
    public function create()
    {
        $drivers = Driver::where('status', 'Active')->orderBy('name')->get(['id', 'name']);

        $passenger = Passenger::where('user_id', Auth::id())->firstOrFail();
        $passengers = Passenger::where('approval_status', 'approved')
            ->where('id', '!=', $passenger->id)
            ->orderBy('name')
            ->get(['id', 'name', 'roll']);
        $myComplaints = $passenger->complaints()->latest()->get();

        return view('complaints.create', compact('passenger', 'drivers', 'passengers', 'myComplaints'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateComplaint($request, false);

        $passengerId = Passenger::where('user_id', Auth::id())->firstOrFail()->id;

        Complaint::create([
            'passenger_id'         => $passengerId,
            'type'                 => $validated['type'],
            'against_type'         => $validated['against_type'] ?: 'general',
            'against_driver_id'    => $validated['against_type'] === 'driver' ? $validated['against_driver_id'] : null,
            'against_passenger_id' => $validated['against_type'] === 'passenger' ? $validated['against_passenger_id'] : null,
            'subject'              => $validated['subject'],
            'message'              => $validated['message'],
        ]);

        return redirect()->route('complaints.create')->with('success', 'Your ' . $validated['type'] . ' has been submitted to the admin.');
    }

    // Admin: complete review and management queue — receives complaints only, never creates them.
    public function index()
    {
        $complaints = Complaint::with('passenger', 'againstDriver', 'againstPassenger')
            ->latest()
            ->paginate(20);

        return view('complaints.index', compact('complaints'));
    }

    public function edit(Complaint $complaint)
    {
        $drivers = Driver::where('status', 'Active')->orderBy('name')->get(['id', 'name']);
        $passengers = Passenger::where('approval_status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'roll']);

        return view('complaints.edit', compact('complaint', 'drivers', 'passengers'));
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validated = $this->validateComplaint($request, true);

        $complaint->update([
            'passenger_id'         => $validated['passenger_id'],
            'type'                 => $validated['type'],
            'against_type'         => $validated['against_type'] ?: 'general',
            'against_driver_id'    => $validated['against_type'] === 'driver' ? $validated['against_driver_id'] : null,
            'against_passenger_id' => $validated['against_type'] === 'passenger' ? $validated['against_passenger_id'] : null,
            'subject'              => $validated['subject'],
            'message'              => $validated['message'],
        ]);

        return redirect()->route('complaints.index')->with('success', 'Complaint / feedback record updated.');
    }

    public function respond(Request $request, Complaint $complaint)
    {
        $request->validate([
            'admin_response' => 'required|string|max:2000',
            'status'         => 'required|string|in:open,reviewed,resolved',
        ]);

        $complaint->update([
            'admin_response' => $request->admin_response,
            'status'         => $request->status,
        ]);

        return redirect()->back()->with('success', 'Response saved.');
    }

    public function destroy(Complaint $complaint)
    {
        $complaint->delete();

        return redirect()->route('complaints.index')->with('success', 'Complaint / feedback record removed.');
    }

    private function validateComplaint(Request $request, bool $requirePassenger): array
    {
        return $request->validate([
            'passenger_id'         => [$requirePassenger ? 'required' : 'nullable', 'exists:passengers,id'],
            'type'                 => 'required|string|in:complaint,feedback',
            'against_type'         => 'required|string|in:driver,passenger,general',
            'against_driver_id'    => 'nullable|required_if:against_type,driver|exists:drivers,id',
            'against_passenger_id' => 'nullable|required_if:against_type,passenger|exists:passengers,id',
            'subject'              => 'required|string|max:255',
            'message'              => 'required|string|max:2000',
        ]);
    }
}
