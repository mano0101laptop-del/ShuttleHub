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
            ['name' => 'Ahmad Raza',     'roll' => 'CS-2201-001', 'department' => 'Computer Science', 'passenger_type' => 'Student', 'contact_number' => '0300-1110001', 'emergency_contact' => '0300-9990001', 'address' => 'House 12, Gulberg, Gujranwala', 'stop' => 'Gulberg Chowk',       'route' => 'Route A - Gulberg'],
            ['name' => 'Hassan Ali',     'roll' => 'CS-2201-002', 'department' => 'Computer Science', 'passenger_type' => 'Student', 'contact_number' => '0300-1110002', 'emergency_contact' => '0300-9990002', 'address' => 'House 8, Model Town, Gujranwala', 'stop' => 'Model Town Link Rd',  'route' => 'Route B - Model Town'],
            ['name' => 'Zainab Fatima',  'roll' => 'EE-2201-014', 'department' => 'Electrical Eng.',  'passenger_type' => 'Student', 'contact_number' => '0300-1110014', 'emergency_contact' => '0300-9990014', 'address' => 'House 4, Wapda Town, Gujranwala', 'stop' => 'Wapda Town Phase 1',  'route' => 'Route C - Wapda Town'],
            ['name' => 'Sana Yousaf',    'roll' => 'BBA-2202-007','department' => 'Business Admin.',  'passenger_type' => 'Teacher', 'contact_number' => '0300-1110007', 'emergency_contact' => '0300-9990007', 'address' => 'House 21, Johar Town, Gujranwala', 'stop' => 'Johar Town Block J',  'route' => 'Route D - Johar Town'],
            ['name' => 'Usman Tariq',    'roll' => 'ME-2201-022', 'department' => 'Mechanical Eng.',  'passenger_type' => 'Student', 'contact_number' => '0300-1110022', 'emergency_contact' => '0300-9990022', 'address' => 'House 5, DHA Phase 5, Gujranwala', 'stop' => 'DHA Phase 5 Main',    'route' => 'Route E - DHA Phase 5'],
            ['name' => 'Ayesha Noor',    'roll' => 'CS-2202-031', 'department' => 'Computer Science', 'passenger_type' => 'Staff',   'contact_number' => '0300-1110031', 'emergency_contact' => '0300-9990031', 'address' => 'House 17, Gulberg, Gujranwala', 'stop' => 'Gulberg Main Blvd',   'route' => 'Route A - Gulberg'],
        ];

        foreach ($adminCreated as $p) {
            $routeId = Route::where('name', $p['route'])->value('id');

            Passenger::firstOrCreate(
                ['roll' => $p['roll']],
                [
                    'user_id'              => null,
                    'name'                 => $p['name'],
                    'department'           => $p['department'],
                    'passenger_type'       => $p['passenger_type'],
                    'contact_number'       => $p['contact_number'],
                    'emergency_contact'    => $p['emergency_contact'],
                    'address'              => $p['address'],
                    'stop'                 => $p['stop'],
                    'status'               => 'Active',
                    'approval_status'      => 'approved',
                    'route_id'             => $routeId,
                ]
            );
        }

        // ── Self-registered passengers (own user account, mixed approval states) ──
        $selfRegistered = [
            ['name' => 'Bilal Aslam',   'email' => 'bilal.aslam@student.edu',   'department' => 'Computer Science', 'passenger_type' => 'Student', 'contact_number' => '0301-2220001', 'emergency_contact' => '0301-8880001', 'address' => 'Township Block A, Gujranwala', 'stop' => 'Township Block A',  'route' => null,                      'status' => 'approved'],
            ['name' => 'Mahnoor Khan',  'email' => 'mahnoor.khan@student.edu',  'department' => 'Software Eng.',    'passenger_type' => 'Student', 'contact_number' => '0301-2220002', 'emergency_contact' => '0301-8880002', 'address' => 'DHA Phase 5, Gujranwala', 'stop' => 'DHA Phase 5 Gate 2','route' => 'Route E - DHA Phase 5',   'status' => 'approved'],
            ['name' => 'Faizan Sheikh', 'email' => 'faizan.sheikh@student.edu', 'department' => 'Business Admin.',  'passenger_type' => 'Student', 'contact_number' => '0301-2220003', 'emergency_contact' => '0301-8880003', 'address' => 'Satellite Town, Gujranwala', 'stop' => null,                'route' => null,                      'status' => 'pending'],
            ['name' => 'Rida Zahid',    'email' => 'rida.zahid@student.edu',    'department' => 'Electrical Eng.',  'passenger_type' => 'Student', 'contact_number' => '0301-2220004', 'emergency_contact' => '0301-8880004', 'address' => 'Peoples Colony, Gujranwala', 'stop' => null,                'route' => null,                      'status' => 'pending'],
            ['name' => 'Danish Farooq', 'email' => 'danish.farooq@student.edu', 'department' => 'Mechanical Eng.',  'passenger_type' => 'Student', 'contact_number' => '0301-2220005', 'emergency_contact' => '0301-8880005', 'address' => 'Johar Town Block F, Gujranwala', 'stop' => 'Johar Town Block F','route' => null,                      'status' => 'rejected'],
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
                    'passenger_type'       => $p['passenger_type'],
                    'contact_number'       => $p['contact_number'],
                    'emergency_contact'    => $p['emergency_contact'],
                    'address'              => $p['address'],
                    'stop'                 => $p['stop'],
                    'status'               => $p['status'] === 'approved' ? 'Active' : 'Inactive',
                    'approval_status'      => $p['status'],
                    'route_id'             => $routeId,
                ]
            );
        }
    }
}
