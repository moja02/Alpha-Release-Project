<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // 1. تعديل حقل معرّف المستخدم ليكون اختيارياً (Nullable) لأن الزائر ليس له حساب
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // 2. إضافة حقل منطقي لتمييز الزوار عن المشتركين بشكل سريع في الاستعلامات
            $table->boolean('is_guest')->default(false)->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // إرجاع الحقل لحالته الأصلية وإلغاء الحقل المضاف عند التراجع
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn('is_guest');
        });
    }
};