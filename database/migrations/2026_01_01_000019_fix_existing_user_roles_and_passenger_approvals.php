<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Safety-net migration.
 *
 * Fixes users whose role was incorrectly defaulted to 'passenger' by the
 * old _017 migration, but ONLY if they have no linked Passenger record AND
 * their role is still 'passenger' (meaning it was never explicitly set).
 *
 * Users explicitly registered as 'incharge' or 'driver' are
 * left untouched — they will not have a Passenger record by design.
 *
 * Also marks any passenger records with no user_id (admin-created before
 * the approval system) as 'approved'.
 *
 * Safe to run multiple times (idempotent).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Find users with no passenger record whose role is STILL 'passenger'.
        // These are accounts that were created before the role system existed
        // and got the wrong default. Do NOT touch incharge/driver accounts —
        // they legitimately have no passenger record.
        $wronglyDefaultedIds = DB::table('users')
            ->leftJoin('passengers', 'users.id', '=', 'passengers.user_id')
            ->whereNull('passengers.id')        // no linked passenger record
            ->where('users.role', 'passenger')  // but wrongly tagged as passenger
            ->pluck('users.id');

        if ($wronglyDefaultedIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('id', $wronglyDefaultedIds)
                ->update(['role' => 'admin']);
        }

        // Passenger records with no user_id were admin-created → auto-approve them
        DB::table('passengers')
            ->whereNull('user_id')
            ->where(function ($q) {
                $q->whereNull('approval_status')
                  ->orWhere('approval_status', 'pending');
            })
            ->update(['approval_status' => 'approved']);
    }

    public function down(): void
    {
        // Non-reversible data fix — intentionally left blank
    }
};
