<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// مين اقترح موعد الاستلام (المالك عند القبول، أو الباحث لما يقترح موعد جديد)
// الطرف التاني هو اللي بيأكد الموعد
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('proposed_by')->nullable()->after('request_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proposed_by');
        });
    }
};
