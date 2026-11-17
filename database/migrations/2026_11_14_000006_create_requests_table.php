<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->text('requester_note')->nullable();
            $table->text('owner_note')->nullable();
            $table->unsignedInteger('count')->default(1);
            $table->enum('status', ['waiting', 'approved', 'rejected', 'canceled', 'received', 'finished'])->default('waiting');
            $table->timestamp('request_date')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
