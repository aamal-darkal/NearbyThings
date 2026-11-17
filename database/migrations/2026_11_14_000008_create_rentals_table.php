<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('requests')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('expected_return_date');
            $table->date('actual_return_date')->nullable();
            $table->enum('return_status', ['pending', 'confirmed', 'disputed', 'auto_confirmed'])->default('pending');
            $table->timestamp('return_reported_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
