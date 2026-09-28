<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        Schema::table('passengers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('drivers', function (Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->softDeletes();
        });

        
        Schema::table('passengers', function (Blueprint $table) {
            $table->index('status');
            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['approval_status']);
            $table->dropSoftDeletes();
        });
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
