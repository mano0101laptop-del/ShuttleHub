<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Passenger;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $passengers = Passenger::where('approval_status', 'approved')
            ->where('status', 'Active')
            ->get();

        if ($passengers->isEmpty()) {
            return;
        }

        $methods = ['manual', 'qr', 'fingerprint'];

        // Build the last 14 days, skipping Fridays/Saturdays (weekend for this campus).
        $days = collect(range(13, 0))
            ->map(fn ($daysAgo) => Carbon::today()->subDays($daysAgo))
            ->filter(fn (Carbon $day) => ! $day->isFriday() && ! $day->isSaturday());

        foreach ($passengers as $passenger) {
            foreach ($days as $day) {
                // ~88% attendance rate per passenger per day.
                $isPresent = fake()->boolean(88);

                Attendance::updateOrCreate(
                    ['passenger_id' => $passenger->id, 'date' => $day->toDateString()],
                    [
                        'status' => $isPresent ? 'Present' : 'Absent',
                        'time'   => $isPresent
                            ? $day->copy()->setTime(fake()->numberBetween(7, 8), fake()->numberBetween(0, 59))->format('H:i:s')
                            : null,
                        'method' => $isPresent ? fake()->randomElement($methods) : 'manual',
                    ]
                );
            }
        }
    }
}
