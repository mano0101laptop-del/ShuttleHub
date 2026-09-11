<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Driver;
use App\Models\Passenger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    // ── Passenger: form to submit a complaint or feedback ──
    public function create()
    {
        $passenger = Passenger::where('user_id', Auth::id())->firstOrFail();

        $drivers    = Driver::where('status', 'Active')->orderBy('name')->get(['id', 'name']);
        $passengers = Passenger::where('id', '!=', $passenger->id)
            ->where('approval_status', 'approved')
            ->orderBy('name')
            ->get(['id', 'name', 'roll']);

        $myComplaints = $passenger->complaints()->latest()->get();

        return view('complaints.create', compact('passenger', 'drivers', 'passengers', 'myComplaints'));
    }

    public function store(Request $request)
    {
        $passenger = Passenger::where('user_id', Auth::id())->firstOrFail();

        $request->validate([
            'type'                  => 'required|string|in:complaint,feedback',
            'against_type'          => 'nullable|string|in:driver,passenger,general',
            'against_driver_id'     => 'nullable|required_if:against_type,driver|exists:drivers,id',
            'against_passenger_id'  => 'nullable|required_if:against_type,passenger|exists:passengers,id',
            'subject'               => 'required|string|max:255',
            'message'               => 'required|string|max:2000',
        ]);

        Complaint::create([
            'passenger_id'          => $passenger->id,
            'type'                  => $request->type,
            'against_type'          => $request->against_type ?: 'general',
            'against_driver_id'     => $request->against_type === 'driver' ? $request->against_driver_id : null,
            'against_passenger_id'  => $request->against_type === 'passenger' ? $request->against_passenger_id : null,
            'subject'               => $request->subject,
            'message'               => $request->message,
        ]);

        return redirect()->route('complaints.create')->with('success', 'Your ' . $request->type . ' has been submitted to the admin.');
    }

    // ── Admin: review queue ──
    public function index()
    {
        $complaints = Complaint::with('passenger', 'againstDriver', 'againstPassenger')
            ->latest()
            ->paginate(20);

        return view('complaints.index', compact('complaints'));
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
}
