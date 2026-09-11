<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Passenger;
use Carbon\Carbon;

/**
 * Attendance reporting — Admin's replacement for the old attendance
 * management screen. Admin (and Incharge) can only VIEW aggregated
 * attendance figures here (daily / weekly / monthly / yearly); actually
 * marking attendance lives on the separate, more restricted
 * AttendanceController screen (incharge/driver/scanner only).
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->query('period', 'daily');
        if (!in_array($period, ['daily', 'weekly', 'monthly', 'yearly'], true)) {
            $period = 'daily';
        }

        $today = now();

        // Overall headline stats (all-time)
        $totalRecords = Attendance::count();
        $totalPresent = Attendance::where('status', 'Present')->count();
        $stats = [
            'total_passengers'  => Passenger::where('status', 'Active')->count(),
            'total_records'     => $totalRecords,
            'avg_attendance'    => $totalRecords > 0 ? round(($totalPresent / $totalRecords) * 100) . '%' : '—',
        ];

        // Grouped in PHP rather than with DATE_FORMAT()/date-part SQL functions,
        // which are MySQL-specific and would silently break this page on any
        // other database driver.
        $rows = match ($period) {
            'daily'   => $this->dailyRows($today),
            'weekly'  => $this->weeklyRows($today),
            'monthly' => $this->monthlyRows($today),
            'yearly'  => $this->yearlyRows($today),
        };

        return view('reports.index', compact('stats', 'rows', 'period'));
    }

    /** Last 14 individual days. */
    private function dailyRows(Carbon $today): array
    {
        $from = $today->copy()->subDays(13)->startOfDay();

        $records = Attendance::where('date', '>=', $from->toDateString())->get(['date', 'status']);

        $rows = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $dayRecords = $records->filter(fn ($r) => $r->date->isSameDay($day));
            $rows[] = $this->summaryRow($day->format('d M Y (D)'), $dayRecords);
        }

        return $rows;
    }

    /** Last 8 calendar weeks (Mon–Sun). */
    private function weeklyRows(Carbon $today): array
    {
        $from = $today->copy()->subWeeks(7)->startOfWeek();

        $records = Attendance::where('date', '>=', $from->toDateString())->get(['date', 'status']);

        $rows = [];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = $today->copy()->subWeeks($i)->startOfWeek();
            $weekEnd   = $weekStart->copy()->endOfWeek();
            $weekRecords = $records->filter(fn ($r) => $r->date->between($weekStart, $weekEnd));
            $label = $weekStart->format('d M') . ' – ' . $weekEnd->format('d M Y');
            $rows[] = $this->summaryRow($label, $weekRecords);
        }

        return $rows;
    }

    /** Last 12 calendar months. */
    private function monthlyRows(Carbon $today): array
    {
        $from = $today->copy()->subMonths(11)->startOfMonth();

        $records = Attendance::where('date', '>=', $from->toDateString())->get(['date', 'status']);

        $rows = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = $today->copy()->subMonths($i);
            $monthRecords = $records->filter(fn ($r) => $r->date->isSameMonth($month));
            $rows[] = $this->summaryRow($month->format('F Y'), $monthRecords);
        }

        return $rows;
    }

    /** Last 5 calendar years. */
    private function yearlyRows(Carbon $today): array
    {
        $from = $today->copy()->subYears(4)->startOfYear();

        $records = Attendance::where('date', '>=', $from->toDateString())->get(['date', 'status', 'passenger_id']);

        $rows = [];
        for ($i = 4; $i >= 0; $i--) {
            $year = $today->copy()->subYears($i);
            $yearRecords = $records->filter(fn ($r) => $r->date->isSameYear($year));
            $rows[] = $this->summaryRow((string) $year->year, $yearRecords);
        }

        return $rows;
    }

    private function summaryRow(string $label, $records): array
    {
        $total   = $records->count();
        $present = $records->where('status', 'Present')->count();

        return [
            'label'      => $label,
            'total'      => $total,
            'present'    => $present,
            'absent'     => $total - $present,
            'attendance' => $total > 0 ? round(($present / $total) * 100) . '%' : '—',
        ];
    }
}
