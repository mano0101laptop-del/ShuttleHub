<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the extra Passenger Information fields collected on the
 * registration form (contact number, passenger type, address and
 * emergency contact). `department` is relaxed to nullable because the
 * public registration form no longer collects it directly — the
 * broader "Passenger Type" (Student / Teacher / Staff) field replaces
 * it for self-registration, while admin-created records can still set
 * a department when relevant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->string('contact_number')->nullable()->after('roll');
            $table->string('passenger_type')->nullable()->after('contact_number'); // Student, Teacher, Staff
            $table->string('address')->nullable()->after('department');
            $table->string('emergency_contact')->nullable()->after('address');
        });

        Schema::table('passengers', function (Blueprint $table) {
            $table->string('department')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropColumn(['contact_number', 'passenger_type', 'address', 'emergency_contact']);
        });

        Schema::table('passengers', function (Blueprint $table) {
            $table->string('department')->nullable(false)->change();
        });
    }
};
