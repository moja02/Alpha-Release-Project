<?php

namespace App\Strategies\Refund;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * صنف استراتيجية الاسترجاع لكبار الشخصيات (VIP).
 * يمنح هذا الصنف ميزات تفضيلية ونسب استرجاع أعلى للمشتركين ذوي الفئة الخاصة.
 */
class VipRefundStrategy implements RefundStrategyInterface
{
    /**
     * حساب مبلغ الاسترجاع المالي للحجز الخاص بكبار الشخصيات.
     *
     * @param float $originalCost التكلفة الأصلية للحجز
     * @param int $minutesToStart عدد الدقائق المتبقية لبدء الحجز
     * @return float القيمة المالية المسترجعة
     */
    public function calculateRefund(float $originalCost, int $minutesToStart): float
    {
        try {
            // التحقق من الوقت المتبقي لحساب نسبة الاسترجاع لمشتركي VIP
            if ($minutesToStart > 30) {
                // استرجاع كامل المبلغ بنسبة 100% إذا كان الإلغاء مبكراً
                return $originalCost;
            }
            
            // في حال الإلغاء المتأخر (أقل من أو يساوي 30 دقيقة)، يتم إرجاع 80% من المبلغ كـ ميزة تفضيلية لكبار الشخصيات
            return $originalCost * 0.8;
        } catch (Exception $exception) {
            // تسجيل الخطأ في ملفات السجل للمساعدة في تشخيص الأعطال
            Log::error("خطأ أثناء حساب استرجاع VIP: " . $exception->getMessage());
            
            // حماية للعمليات المالية: إرجاع قيمة صفرية في حال حدوث استثناء مفاجئ
            return 0.0;
        }
    }
}
