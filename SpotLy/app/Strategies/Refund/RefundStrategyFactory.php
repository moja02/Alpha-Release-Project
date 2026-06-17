<?php

namespace App\Strategies\Refund;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * صنف مصنع استراتيجيات الاسترجاع المبسط.
 * يقوم هذا الصنف بإنشاء وإرجاع استراتيجية الاسترجاع القياسية بعد حذف استراتيجيات الزوار و كبار الشخصيات.
 */
class RefundStrategyFactory
{
    /**
     * تحديد استراتيجية الاسترجاع القياسية وتوليد كائن منها.
     *
     * @param Booking $booking كائن الحجز
     * @return RefundStrategyInterface الاستراتيجية المحددة للتعامل مع هذا الحجز
     */
    public static function make(Booking $booking): RefundStrategyInterface
    {
        try {
            // اعتماد استراتيجية الاسترجاع القياسية دائماً بعد حذف الاستراتيجيات الأخرى
            return new StandardRefundStrategy();
        } catch (Exception $exception) {
            // توثيق الخطأ الحاصل لتسهيل الصيانة البرمجية
            Log::error("خطأ أثناء توليد استراتيجية الاسترجاع في المصنع: " . $exception->getMessage());
            
            // إرجاع الاستراتيجية القياسية كخيار آمن وتفادي تعطل النظام
            return new StandardRefundStrategy();
        }
    }
}
