<?php

namespace Database\Seeders;

use App\Models\Passenger;
use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PassengerSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin-created passengers (no linked user account, auto-approved) ──
        $adminCreated = [
            ['name' => 'Ahmad Raza',     'roll' => 'CS-2201-001', 'department' => 'Computer Science', 'stop' => 'Gulberg Chowk',       'route' => 'Route A - Gulberg'],
            ['name' => 'Hassan Ali',     'roll' => 'CS-2201-002', 'department' => 'Computer Science', 'stop' => 'Model Town Link Rd',  'route' => 'Route B - Model Town'],
            ['name' => 'Zainab Fatima',  'roll' => 'EE-2201-014', 'department' => 'Electrical Eng.',  'stop' => 'Wapda Town Phase 1',  'route' => 'Route C - Wapda Town'],
            ['name' => 'Sana Yousaf',    'roll' => 'BBA-2202-007','department' => 'Business Admin.',  'stop' => 'Johar Town Block J',  'route' => 'Route D - Johar Town'],
            ['name' => 'Usman Tariq',    'roll' => 'ME-2201-022', 'department' => 'Mechanical Eng.',  'stop' => 'DHA Phase 5 Main',    'route' => 'Route E - DHA Phase 5'],
            ['name' => 'Ayesha Noor',    'roll' => 'CS-2202-031', 'department' => 'Computer Science', 'stop' => 'Gulberg Main Blvd',   'route' => 'Route A - Gulberg'],
        ];

        foreach ($adminCreated as $p) {
            $routeId = Route::where('name', $p['route'])->value('id');

            Passenger::firstOrCreate(
                ['roll' => $p['roll']],
                [
                    'user_id'              => null,
                    'name'                 => $p['name'],
                    'department'           => $p['department'],
                    'stop'                 => $p['stop'],
                    'status'               => 'Active',
                    'approval_status'      => 'approved',
                    'route_id'             => $routeId,
                    'qr_token'             => Passenger::generateQrToken(),
                    'fingerprint_hash'     => Hash::make('seeded-fingerprint-' . $p['roll']),
                    'fingerprint_enrolled' => true,
                ]
            );
        }

        // ── Self-registered passengers (own user account, mixed approval states) ──
        $selfRegistered = [
            ['name' => 'Bilal Aslam',   'email' => 'bilal.aslam@student.edu',   'department' => 'Computer Science', 'stop' => 'Township Block A',  'route' => null,                      'status' => 'approved'],
            ['name' => 'Mahnoor Khan',  'email' => 'mahnoor.khan@student.edu',  'department' => 'Software Eng.',    'stop' => 'DHA Phase 5 Gate 2','route' => 'Route E - DHA Phase 5',   'status' => 'approved'],
            ['name' => 'Faizan Sheikh', 'email' => 'faizan.sheikh@student.edu', 'department' => 'Business Admin.',  'stop' => null,                'route' => null,                      'status' => 'pending'],
            ['name' => 'Rida Zahid',    'email' => 'rida.zahid@student.edu',    'department' => 'Electrical Eng.',  'stop' => null,                'route' => null,                      'status' => 'pending'],
            ['name' => 'Danish Farooq', 'email' => 'danish.farooq@student.edu', 'department' => 'Mechanical Eng.',  'stop' => 'Johar Town Block F','route' => null,                      'status' => 'rejected'],
        ];

        foreach ($selfRegistered as $p) {
            $user = User::firstOrCreate(
                ['email' => $p['email']],
                [
                    'name'     => $p['name'],
                    'password' => Hash::make('password'),
                    'role'     => 'passenger',
                ]
            );

            $routeId = $p['route'] ? Route::where('name', $p['route'])->value('id') : null;

            Passenger::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name'                 => $p['name'],
                    'roll'                 => 'REG-' . str_pad($user->id, 5, '0', STR_PAD_LEFT),
                    'department'           => $p['department'],
                    'stop'                 => $p['stop'],
                    'status'               => $p['status'] === 'approved' ? 'Active' : 'Inactive',
                    'approval_status'      => $p['status'],
                    'route_id'             => $routeId,
                    'qr_token'             => Passenger::generateQrToken(),
                    'fingerprint_hash'     => Hash::make('seeded-fingerprint-' . $user->id),
                    'fingerprint_enrolled' => true,
                ]
            );
        }
    }
}
