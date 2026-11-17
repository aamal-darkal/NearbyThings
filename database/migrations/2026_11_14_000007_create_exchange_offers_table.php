<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('offered_item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->text('offered_description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_offers');
    }
};
