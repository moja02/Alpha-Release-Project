<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * تشغيل الهجرة لتحديث قاعدة البيانات.
     */
    public function up(): void
    {
        try {
            // تعديل جدول الحجوزات لإضافة حقول تتبع التكاليف والتعويضات المسترجعة
            Schema::table('bookings', function (Blueprint $table) {
                // حقل لتخزين التكلفة الأصلية للحجز (مثال: الساعات الفعالة مضروبة بسعر الساعة)
                $table->decimal('cost', 10, 2)->default(0.00)->after('type');
                
                // حقل لتخزين قيمة النقاط المسترجعة كتعويض للمستخدم في حال إلغاء الحجز
                $table->decimal('refund_amount', 10, 2)->default(0.00)->after('cost');
            });
        } catch (\Exception $exception) {
            // توثيق الخطأ في السجلات لمتابعة الصيانة أو تصحيح الأخطاء لاحقاً
            Log::error('فشل في تنفيذ هجرة إضافة الحقول لجدول bookings: ' . $exception->getMessage());
        }
    }

    /**
     * التراجع عن الهجرة وإعادة الجدول لحالته السابقة.
     */
    public function down(): void
    {
        try {
            // إزالة الحقول التي تمت إضافتها للتراجع عن التحديث
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn(['cost', 'refund_amount']);
            });
        } catch (\Exception $exception) {
            // توثيق أي خطأ يحدث أثناء التراجع
            Log::error('فشل في التراجع عن هجرة جدول bookings: ' . $exception->getMessage());
        }
    }
};

