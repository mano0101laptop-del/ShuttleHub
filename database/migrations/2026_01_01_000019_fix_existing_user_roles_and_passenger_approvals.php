<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        
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
        
    }
};
