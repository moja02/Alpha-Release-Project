<?php

namespace App\Strategies\Refund;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * صنف استراتيجية الاسترجاع للزوار (Guests).
 * يطبق هذا الصنف قيوداً صارمة على المبالغ المسترجعة للزوار لعدم وجود حساب دائم لهم.
 */
class GuestRefundStrategy implements RefundStrategyInterface
{
    /**
     * حساب مبلغ الاسترجاع المالي للحجز الخاص بالزوار.
     *
     * @param float $originalCost التكلفة الأصلية للحجز
     * @param int $minutesToStart عدد الدقائق المتبقية لبدء الحجز
     * @return float القيمة المالية المسترجعة
     */
    public function calculateRefund(float $originalCost, int $minutesToStart): float
    {
        try {
            // التحقق من المدة الزمنية المتبقية لحجز الزائر
            if ($minutesToStart > 30) {
                // إذا ألغى الزائر قبل أكثر من 30 دقيقة، يسترجع المبلغ كاملاً بنسبة 100%
                return $originalCost;
            }
            
            // في حال الإلغاء المتأخر (أقل من أو يساوي 30 دقيقة)، لا يستحق الزائر أي مبلغ مسترجع (0%)
            return 0.0;
        } catch (Exception $exception) {
            // توثيق الخطأ الحاصل لضمان سهولة الصيانة
            Log::error("خطأ أثناء حساب استرجاع الزائر: " . $exception->getMessage());
            
            // إرجاع قيمة صفرية لمنع الأخطاء التراكمية
            return 0.0;
        }
    }
}
