<?php

namespace App\Strategies\Refund;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * صنف مصنع استراتيجيات الاسترجاع.
 * يقوم هذا الصنف بإنشاء وتحديد الاستراتيجية المناسبة للحجز بناءً على خصائص الحجز وبيانات المستخدم.
 */
class RefundStrategyFactory
{
    /**
     * تحديد استراتيجية الاسترجاع المناسبة وتوليد كائن منها.
     *
     * @param Booking $booking كائن الحجز
     * @return RefundStrategyInterface الاستراتيجية المحددة للتعامل مع هذا الحجز
     */
    public static function make(Booking $booking): RefundStrategyInterface
    {
        try {
            // التحقق مما إذا كان الحجز تابعاً لزائر
            if ($booking->is_guest) {
                // استخدام استراتيجية استرجاع الزوار
                return new GuestRefundStrategy();
            }

            // جلب السائق/المستخدم المرتبط بالحجز الحالي
            $user = $booking->user;
            
            // إذا كان المستخدم مسجلاً ولديه حالة كبار الشخصيات VIP
            if ($user && $user->status === 'vip') {
                // استخدام استراتيجية استرجاع VIP الخاصة
                return new VipRefundStrategy();
            }

            // الاستراتيجية الافتراضية للمشتركين العاديين
            return new StandardRefundStrategy();
        } catch (Exception $exception) {
            // توثيق الخطأ الحاصل لتسهيل عملية إصلاح المشاكل المستقبلية
            Log::error("خطأ أثناء توليد استراتيجية الاسترجاع في المصنع: " . $exception->getMessage());
            
            // تفادي تعطل النظام بإرجاع الاستراتيجية الافتراضية (Standard) كخيار آمن
            return new StandardRefundStrategy();
        }
    }
}
