<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::with('vehicle.driver', 'passengers', 'routeStops')->latest()->paginate(15)->withQueryString();
        return view('routes.index', compact('routes'));
    }

    public function create()
    {
        $vehicles = Vehicle::where('status', 'Active')->get();
        return view('routes.create', compact('vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRoute($request);

        DB::transaction(function () use ($request, $validated) {
            $route = Route::create([
                'name'       => $validated['name'],
                'from'       => $validated['from'],
                'to'         => $validated['to'],
                'stops'      => 1, // recalculated below once real stops are saved
                'status'     => $validated['status'],
                'vehicle_id' => $validated['vehicle_id'] ?? null,
            ]);

            $this->syncStops($route, $request);
        });

        return redirect()->route('tmsroutes.index')->with('success', 'Route added successfully!');
    }

    public function edit(Route $tmsroute)
    {
        $vehicles = Vehicle::where('status', 'Active')->get();
        $tmsroute->load('routeStops');
        return view('routes.edit', ['tmsroute' => $tmsroute, 'vehicles' => $vehicles]);
    }

    public function update(Request $request, Route $tmsroute)
    {
        $validated = $this->validateRoute($request);

        DB::transaction(function () use ($request, $validated, $tmsroute) {
            $tmsroute->update([
                'name'       => $validated['name'],
                'from'       => $validated['from'],
                'to'         => $validated['to'],
                'status'     => $validated['status'],
                'vehicle_id' => $validated['vehicle_id'] ?? null,
            ]);

            $this->syncStops($tmsroute, $request);
        });

        return redirect()->route('tmsroutes.index')->with('success', 'Route updated successfully!');
    }

    public function destroy(Route $tmsroute)
    {
        // route_stops are removed automatically via cascadeOnDelete()
        $tmsroute->delete();
        return redirect()->route('tmsroutes.index')->with('success', 'Route deleted successfully!');
    }

    private function validateRoute(Request $request): array
    {
        return $request->validate([
            'name'          => 'required|string|max:255',
            'from'          => 'required|string',
            'to'            => 'required|string',
            'status'        => 'required|string|in:Active,Inactive',
            'vehicle_id'    => 'nullable|exists:vehicles,id',
            'stop_name'     => 'nullable|array',
            'stop_name.*'   => 'nullable|string|max:255',
            'stop_eta'      => 'nullable|array',
            'stop_eta.*'    => 'nullable|string|max:50',
        ]);
    }

    /**
     * Replace this route's stops with whatever was submitted in the form,
     * in the order they were submitted (that order becomes the sequence).
     * Blank stop names are dropped. If nothing valid was submitted, a single
     * stop matching the route's "to" destination is kept so the route
     * always has at least one stop.
     */
    private function syncStops(Route $route, Request $request): void
    {
        $names = $request->input('stop_name', []);
        $etas  = $request->input('stop_eta', []);

        $route->routeStops()->delete();

        $sequence = 1;
        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            RouteStop::create([
                'route_id' => $route->id,
                'name'     => $name,
                'eta'      => trim((string) ($etas[$i] ?? '')) ?: null,
                'sequence' => $sequence++,
            ]);
        }

        if ($sequence === 1) {
            // nothing valid was submitted — fall back to the route's "to" field
            RouteStop::create([
                'route_id' => $route->id,
                'name'     => $route->to,
                'sequence' => 1,
            ]);
        }

        $route->syncStopsCount();
    }
}
