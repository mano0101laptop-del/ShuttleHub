<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('complaint'); // complaint, feedback
            $table->string('against_type')->nullable();   // driver, passenger, general
            $table->foreignId('against_driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->foreignId('against_passenger_id')->nullable()->constrained('passengers')->nullOnDelete();
            $table->string('subject');
            $table->text('message');
            $table->string('status')->default('open'); // open, reviewed, resolved
            $table->text('admin_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
