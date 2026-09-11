<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('from');
            $table->string('to');
            $table->unsignedInteger('stops')->default(1);
            $table->string('status')->default('Active'); // Active, Inactive
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
