<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('driver', 'route')->latest()->paginate(15)->withQueryString();
        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'number'   => 'required|string|unique:vehicles,number',
            'type'     => 'required|string',
            'capacity' => 'required|integer|min:1',
            'status'   => 'required|string|in:Active,Inactive,Breakdown',
        ]);

        Vehicle::create($request->only('number', 'type', 'capacity', 'status'));

        return redirect()->route('vehicles.index')->with('success', 'Vehicle added successfully!');
    }

    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'number'   => 'required|string|unique:vehicles,number,' . $vehicle->id,
            'type'     => 'required|string',
            'capacity' => 'required|integer|min:1',
            'status'   => 'required|string|in:Active,Inactive,Breakdown',
        ]);

        $vehicle->update($request->only('number', 'type', 'capacity', 'status'));

        return redirect()->route('vehicles.index')->with('success', 'Vehicle updated successfully!');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')->with('success', 'Vehicle deleted successfully!');
    }
}
