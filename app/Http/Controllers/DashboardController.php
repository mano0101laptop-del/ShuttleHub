<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\Driver;
use App\Models\FeePayment;
use App\Models\Message;
use App\Models\Passenger;
use App\Models\Route;
use App\Models\Schedule;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'passenger') {
            $passenger = Passenger::with([
                    'route.vehicle.driver', 'route.Stops', 'Stop',
                    'assignedDriver', 'assignedVehicle',
                ])
                ->where('user_id', $user->id)
                ->first();

            $announcements = Announcement::forAudience('passenger')->latest()->take(5)->get();

            if (!$passenger) {
                return view('dashboard.passenger', ['passenger' => null, 'announcements' => $announcements]);
            }

            $scheduleRelations = [
                'route.Stops', 'driver', 'vehicle', 'stops.Stop',
                'passengerAssignments.Stop',
            ];

            $schedule = Schedule::current()
                ->with($scheduleRelations)
                ->whereHas('passengerAssignments', fn ($query) => $query->where('passenger_id', $passenger->id))
                ->first();

            if (!$schedule && $passenger->route_id) {
                $schedule = Schedule::current()
                    ->with($scheduleRelations)
                    ->where('route_id', $passenger->route_id)
                    ->first();
            }

            return view('dashboard.passenger', compact(
                'passenger', 'announcements', 'schedule'
            ));
        }

        if ($user->role === 'driver') {
            $driver = Driver::with(['vehicle.route.Stops'])->where('user_id', $user->id)->first();
            $announcements = Announcement::forAudience('driver')->latest()->take(5)->get();
            $unreadMessages = Message::where('driver_user_id', $user->id)
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();
            $recentMessages = Message::where('driver_user_id', $user->id)->latest()->take(5)->get();

            $schedule = null;
            if ($driver) {
                $schedule = Schedule::current()
                    ->with([
                        'route.Stops', 'vehicle', 'stops.Stop',
                        'passengerAssignments.passenger', 'passengerAssignments.Stop',
                    ])
                    ->where('driver_id', $driver->id)
                    ->first();
            }

            return view('dashboard.driver', compact(
                'driver', 'announcements', 'unreadMessages', 'recentMessages', 'schedule'
            ));
        }

        if ($user->role === 'incharge') {
            return $this->inchargeDashboard();
        }

        return $this->adminDashboard();
    }

    private function inchargeDashboard()
    {
        $stats = [
            'passengers' => Passenger::where('approval_status', 'approved')->where('status', 'Active')->count(),
            'routes'     => Route::where('status', 'Active')->count(),
            'schedules'  => Schedule::current()->count(),
        ];

        $schedules = Schedule::current()
            ->with(['route', 'driver', 'vehicle'])
            ->get()
            ->sortBy(fn ($schedule) => $schedule->route->name ?? '');

        $announcements = Announcement::with('creator')->latest()->take(5)->get();

        return view('dashboard.incharge', compact('stats', 'schedules', 'announcements'));
    }

    private function adminDashboard()
    {
        $pendingCount = Passenger::where('approval_status', 'pending')->count();
        $pendingFeeCount = FeePayment::where('status', 'pending')->count();
        $pendingCancellations = Passenger::where('cancellation_status', 'requested')->count();
        $openComplaints = Complaint::where('status', 'open')->count();

        $stats = [
            'total_vehicles'        => Vehicle::count(),
            'active_drivers'        => Driver::where('status', 'Active')->count(),
            'total_passengers'      => Passenger::where('approval_status', 'approved')->count(),
            'active_routes'         => Route::where('status', 'Active')->count(),
            'pending_requests'      => $pendingCount,
            'pending_fee_payments'  => $pendingFeeCount,
            'pending_cancellations' => $pendingCancellations,
            'open_complaints'       => $openComplaints,
        ];

        // Recent fee-payment activity (approved/rejected/pending) — the most
        // recently touched fee records, newest first.
        $recent_activity = FeePayment::with('passenger')
            ->whereDate('updated_at', today())
            ->latest('updated_at')
            ->take(10)
            ->get()
            ->map(fn (FeePayment $payment) => [
                'id'        => $payment->id,
                'event'     => 'Fee Payment — ' . ucfirst($payment->status),
                'passenger' => $payment->passenger->name ?? '—',
                'month'     => $payment->monthLabel(),
                'time'      => $payment->updated_at?->format('h:i A') ?? '—',
                'status'    => $payment->status === 'approved' ? 'Active' : ($payment->status === 'rejected' ? 'Issue' : 'Done'),
            ])->toArray();

        if (empty($recent_activity)) {
            $recent_activity = [
                ['id' => 1, 'event' => 'No Activity Today', 'passenger' => '—', 'month' => '—', 'time' => '—', 'status' => 'Done'],
            ];
        }

        $schedules = Schedule::current()
            ->with(['route', 'driver', 'vehicle', 'passengerAssignments'])
            ->get()
            ->sortBy(fn ($schedule) => $schedule->route->name ?? '');

        $unassignedRoutes = Route::where('status', 'Active')
            ->whereNotIn('id', $schedules->pluck('route_id'))
            ->count();

        return view('dashboard.index', compact('stats', 'recent_activity', 'schedules', 'unassignedRoutes'));
    }
}
