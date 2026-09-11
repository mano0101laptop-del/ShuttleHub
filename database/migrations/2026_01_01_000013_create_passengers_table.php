<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('roll')->unique();
            $table->string('department');
            $table->string('stop')->nullable();
            $table->string('status')->default('Active'); // Active, Inactive
            $table->foreignId('route_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passengers');
    }
};
