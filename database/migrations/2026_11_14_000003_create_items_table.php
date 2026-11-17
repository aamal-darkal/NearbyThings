<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('condition_id')->constrained('conditions')->restrictOnDelete();
            $table->enum('transaction_type', ['sale', 'exchange', 'rent']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedInteger('count')->default(1);
            $table->enum('state', ['active', 'inactive', 'banned', 'sold', 'rented'])->default('active');
            $table->string('location_hint')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('variants')->nullable();
            $table->unsignedInteger('max_rent_duration')->nullable();
            $table->enum('rent_unit', ['day', 'month', 'year'])->nullable();
            $table->timestamps();

            $table->index(['state', 'transaction_type']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
