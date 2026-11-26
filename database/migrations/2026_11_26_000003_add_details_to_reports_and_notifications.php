<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// البلاغات: نوع المخالفة وملاحظة المشرف
// الإشعارات: رابط للصفحة المرتبطة (مثل صفحة الطلب)
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('category', 20)->default('other')->after('request_id')->comment('fraud / prohibited / other');
            $table->text('admin_note')->nullable()->after('status');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->string('link')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['category', 'admin_note']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('link');
        });
    }
};
