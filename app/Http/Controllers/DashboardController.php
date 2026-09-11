<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\DailyAssignment;
use App\Models\Driver;
use App\Models\Message;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'passenger') {
            $passenger = Passenger::with([
                    'route.vehicle.driver', 'route.routeStops', 'routeStop',
                    'assignedDriver', 'assignedVehicle', 'attendances',
                ])
                ->where('user_id', $user->id)
                ->first();

            $announcements = Announcement::forAudience('passenger')->latest()->take(5)->get();

            if (!$passenger) {
                return view('dashboard.passenger', ['passenger' => null, 'announcements' => $announcements]);
            }

            $totalDays     = $passenger->attendances()->count();
            $presentDays   = $passenger->attendances()->where('status', 'Present')->count();
            $attendancePct = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;

            $todaysAssignment = DailyAssignment::with([
                    'route.routeStops', 'driver', 'vehicle', 'stops.routeStop',
                    'passengerAssignments.routeStop',
                ])
                ->whereDate('date', today())
                ->whereHas('passengerAssignments', fn ($q) => $q->where('passenger_id', $passenger->id))
                ->first();

            if (!$todaysAssignment && $passenger->route_id) {
                $todaysAssignment = DailyAssignment::with([
                        'route.routeStops', 'driver', 'vehicle', 'stops.routeStop',
                        'passengerAssignments.routeStop',
                    ])
                    ->where('route_id', $passenger->route_id)
                    ->whereDate('date', today())
                    ->first();
            }

            return view('dashboard.passenger', compact(
                'passenger', 'totalDays', 'presentDays', 'attendancePct', 'announcements', 'todaysAssignment'
            ));
        }

        if ($user->role === 'scanner') {
            return redirect()->route('scanner.index');
        }

        if ($user->role === 'driver') {
            $driver = Driver::with(['vehicle.route.routeStops'])->where('user_id', $user->id)->first();
            $announcements = Announcement::forAudience('driver')->latest()->take(5)->get();
            $unreadMessages = Message::where('driver_user_id', $user->id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();
            $recentMessages = Message::where('driver_user_id', $user->id)->latest()->take(5)->get();

            $todaysAssignment = null;
            $upcomingAssignments = collect();

            if ($driver) {
                $assignmentRelations = [
                    'route.routeStops', 'vehicle', 'stops.routeStop',
                    'passengerAssignments.passenger', 'passengerAssignments.routeStop',
                ];

                $todaysAssignment = DailyAssignment::with($assignmentRelations)
                    ->where('driver_id', $driver->id)
                    ->whereDate('date', today())
                    ->first();

                $upcomingAssignments = DailyAssignment::with($assignmentRelations)
                    ->where('driver_id', $driver->id)
                    ->whereDate('date', '>', today())
                    ->where('status', '!=', 'Cancelled')
                    ->orderBy('date')
                    ->take(7)
                    ->get();
            }

            return view('dashboard.driver', compact(
                'driver', 'announcements', 'unreadMessages', 'recentMessages',
                'todaysAssignment', 'upcomingAssignments'
            ));
        }

        $pendingCount = Passenger::where('approval_status', 'pending')->count();
        $pendingFeeCount = \App\Models\FeePayment::where('status', 'pending')->count();
        $pendingCancellations = Passenger::where('cancellation_status', 'requested')->count();
        $openComplaints = \App\Models\Complaint::where('status', 'open')->count();

        $stats = [
            'total_vehicles'          => Vehicle::count(),
            'active_drivers'          => Driver::where('status', 'Active')->count(),
            'total_passengers'        => Passenger::where('approval_status', 'approved')->count(),
            'active_routes'           => Route::where('status', 'Active')->count(),
            'pending_requests'        => $pendingCount,
            'pending_fee_payments'    => $pendingFeeCount,
            'pending_cancellations'   => $pendingCancellations,
            'open_complaints'         => $openComplaints,
        ];

        $recent_activity = Attendance::with('passenger.route.vehicle.driver')
            ->whereDate('created_at', today())
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($a) => [
                'id'      => $a->id,
                'event'   => 'Attendance Marked',
                'vehicle' => optional(optional(optional($a->passenger)->route)->vehicle)->number ?? '—',
                'driver'  => optional(optional(optional(optional($a->passenger)->route)->vehicle)->driver)->name ?? '—',
                'time'    => $a->time ?? '—',
                'status'  => $a->status === 'Present' ? 'Active' : 'Issue',
            ])->toArray();

        if (empty($recent_activity)) {
            $recent_activity = [
                ['id' => 1, 'event' => 'No Activity Today', 'vehicle' => '—', 'driver' => '—', 'time' => '—', 'status' => 'Done'],
            ];
        }

        $todaysAssignments = DailyAssignment::with(['route', 'driver', 'vehicle', 'passengerAssignments'])
            ->whereDate('date', today())
            ->get()
            ->sortBy(fn ($a) => $a->route->name ?? '');

        $unassignedRoutesToday = Route::where('status', 'Active')
            ->whereNotIn('id', $todaysAssignments->pluck('route_id'))
            ->count();

        return view('dashboard.index', compact('stats', 'recent_activity', 'todaysAssignments', 'unassignedRoutesToday'));
    }
}
