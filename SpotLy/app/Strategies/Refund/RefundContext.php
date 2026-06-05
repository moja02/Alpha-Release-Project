<?php

namespace App\Strategies\Refund;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * صنف سياق الاسترجاع المالي.
 * يعمل هذا الصنف كوسيط لاستدعاء حساب الاسترجاع باستخدام الاستراتيجية الممررة إليه.
 */
class RefundContext
{
    /**
     * @var RefundStrategyInterface الاستراتيجية المستخدمة حالياً داخل السياق
     */
    private RefundStrategyInterface $strategy;

    /**
     * بناء كائن السياق وتعيين الاستراتيجية المطلوبة.
     *
     * @param RefundStrategyInterface $strategy الاستراتيجية المراد تطبيقها
     */
    public function __construct(RefundStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * تنفيذ الاستراتيجية لحساب قيمة الاسترجاع المالي.
     *
     * @param float $originalCost التكلفة الأصلية للحجز
     * @param int $minutesToStart عدد الدقائق المتبقية لبدء الحجز
     * @return float القيمة المالية المسترجعة
     */
    public function calculateRefund(float $originalCost, int $minutesToStart): float
    {
        try {
            // تفويض عملية الحساب بالكامل إلى كائن الاستراتيجية النشطة
            return $this->strategy->calculateRefund($originalCost, $minutesToStart);
        } catch (Exception $exception) {
            // توثيق الخطأ لتسهيل العثور على الأخطاء البرمجية وصيانة الكود
            Log::error("خطأ داخل سياق الاسترجاع (RefundContext): " . $exception->getMessage());
            
            // إرجاع قيمة افتراضية آمنة
            return 0.0;
        }
    }
}
