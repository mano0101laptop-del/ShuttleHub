<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();

            $table->string('cnic')->nullable()->after('license');
            $table->date('dob')->nullable()->after('cnic');
            $table->string('address')->nullable()->after('dob');
            $table->string('email')->nullable()->after('address');

            $table->string('photo_path')->nullable()->after('email');
            $table->string('cnic_front_path')->nullable()->after('photo_path');
            $table->string('cnic_back_path')->nullable()->after('cnic_front_path');

            $table->string('reference_name')->nullable()->after('cnic_back_path');
            $table->string('reference_phone')->nullable()->after('reference_name');
            $table->string('reference_relation')->nullable()->after('reference_phone');
            $table->string('reference_address')->nullable()->after('reference_relation');
        });
    }

    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'cnic', 'dob', 'address', 'email',
                'photo_path', 'cnic_front_path', 'cnic_back_path',
                'reference_name', 'reference_phone', 'reference_relation', 'reference_address',
            ]);
        });
    }
};
