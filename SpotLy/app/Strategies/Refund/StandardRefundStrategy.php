<?php

namespace App\Strategies\Refund;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * صنف استراتيجية الاسترجاع القياسية.
 * يقوم هذا الصنف بحساب نسبة الاسترجاع الافتراضية بناءً على المدة الزمنية المتبقية.
 */
class StandardRefundStrategy implements RefundStrategyInterface
{
    /**
     * حساب مبلغ الاسترجاع المالي للحجز القياسي.
     *
     * @param float $originalCost التكلفة الأصلية للحجز
     * @param int $minutesToStart عدد الدقائق المتبقية لبدء الحجز
     * @return float القيمة المالية المسترجعة
     */
    public function calculateRefund(float $originalCost, int $minutesToStart): float
    {
        try {
            // التحقق من الوقت المتبقي لحساب نسبة الاسترجاع القياسية
            if ($minutesToStart > 30) {
                // إذا كان الإلغاء قبل أكثر من 30 دقيقة، يتم إرجاع المبلغ كاملاً بنسبة 100%
                return $originalCost;
            }
            
            // إذا كان الإلغاء قبل 30 دقيقة أو أقل، يتم إرجاع نصف المبلغ بنسبة 50%
            return $originalCost * 0.5;
        } catch (Exception $exception) {
            // تسجيل الخطأ في السجلات لتسهيل الصيانة وتصحيح الأخطاء
            Log::error("خطأ أثناء حساب الاسترجاع القياسي: " . $exception->getMessage());
            
            // إرجاع قيمة افتراضية لتفادي تعطل النظام
            return 0.0;
        }
    }
}
