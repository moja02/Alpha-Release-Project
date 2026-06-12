<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * فئة الخدمة المالية للتعامل مع حسابات الإيرادات والتعويضات.
 */
class FinancialReportService
{
    /**
     * حساب وتجميع الإحصاءات المالية الخاصة بساحات المدير ومقارنتها بالنظام.
     *
     * @param int $managerId معرف المدير المسؤول
     * @return array مصفوفة الإحصاءات المالية والنسب المئوية
     * @throws Exception في حال حدوث أي خطأ غير متوقع أثناء الاستعلام
     */
    public function getFinancialReportData(int $managerId): array
    {
        try {
            // 1. جلب معرفات الساحات المرتبطة بالمدير المحدد من قاعدة البيانات
            $parkingIds = DB::table('parkings')
                ->where('manager_id', $managerId)
                ->pluck('id')
                ->toArray();

            // 2. إجمالي الإيرادات الخاصة بساحات هذا المدير (مجموع شحنات النقاط المعتمدة)
            $totalRevenue = 0.00;
            if (!empty($parkingIds)) {
                $totalRevenue = (float) DB::table('recharge_requests')
                    ->whereIn('parking_id', $parkingIds)
                    ->where('status', 'Approved')
                    ->sum('requested_points');
            }

            // 3. إجمالي النقاط المسترجعة الخاصة بساحات هذا المدير (التعويضات عند الإلغاء)
            $totalRefundedPoints = 0.00;
            if (!empty($parkingIds)) {
                $totalRefundedPoints = (float) DB::table('bookings')
                    ->whereIn('parking_id', $parkingIds)
                    ->where('status', 'cancelled')
                    ->sum('refund_amount');
            }

            // 4. نسبة التعويضات الإجمالية الخاصة بساحات هذا المدير
            $compensationPercentage = 0.00;
            if ($totalRevenue > 0) {
                // نسبة التعويضات = (النقاط المسترجعة / إجمالي الإيرادات) * 100
                $compensationPercentage = round(($totalRefundedPoints / $totalRevenue) * 100, 2);
            }

            // 5. إحصاءات الإيرادات الإجمالية للنظام بالكامل (للمقارنة والتحليل المالي)
            $systemTotalRevenue = (float) DB::table('recharge_requests')
                ->where('status', 'Approved')
                ->sum('requested_points');

            // 6. إجمالي النقاط المسترجعة للنظام بالكامل
            $systemTotalRefundedPoints = (float) DB::table('bookings')
                ->where('status', 'cancelled')
                ->sum('refund_amount');

            // 7. نسبة التعويضات الإجمالية للنظام بالكامل
            $systemCompensationPercentage = 0.00;
            if ($systemTotalRevenue > 0) {
                $systemCompensationPercentage = round(($systemTotalRefundedPoints / $systemTotalRevenue) * 100, 2);
            }

            // 8. حساب حركة شحن النقاط والإيرادات اليومية لآخر 30 يوماً
            $dailyStats = [];
            for ($dayIndex = 29; $dayIndex >= 0; $dayIndex--) {
                $targetDate = now()->subDays($dayIndex)->format('Y-m-d');
                $dailyStats[$targetDate] = [
                    'revenue' => 0.00,
                    'count' => 0
                ];
            }

            if (!empty($parkingIds)) {
                // جلب مجموع الإيرادات وعدد العمليات اليومية من قاعدة البيانات لساحات المدير
                $rechargeData = DB::table('recharge_requests')
                    ->whereIn('parking_id', $parkingIds)
                    ->where('status', 'Approved')
                    ->where('created_at', '>=', now()->subDays(30)->startOfDay())
                    ->select(
                        DB::raw('DATE(created_at) as recharge_date'),
                        DB::raw('SUM(requested_points) as daily_revenue'),
                        DB::raw('COUNT(id) as daily_count')
                    )
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->get();

                // ملء البيانات المستخرجة في المصفوفة الزمنية الافتراضية
                foreach ($rechargeData as $row) {
                    if (isset($dailyStats[$row->recharge_date])) {
                        $dailyStats[$row->recharge_date]['revenue'] = (float) $row->daily_revenue;
                        $dailyStats[$row->recharge_date]['count'] = (int) $row->daily_count;
                    }
                }
            }

            $dailyLabels = [];
            $dailyRevenueValues = [];
            $dailyCountValues = [];

            // إعادة صياغة وهيكلة البيانات لتكون جاهزة للمخطط البياني (Chart.js)
            foreach ($dailyStats as $dateKey => $statsValues) {
                // تنسيق التاريخ ليكون بالشكل "Month-Day" مثلاً "06-12"
                $dailyLabels[] = \Carbon\Carbon::parse($dateKey)->format('m-d');
                $dailyRevenueValues[] = $statsValues['revenue'];
                $dailyCountValues[] = $statsValues['count'];
            }

            // إرجاع مصفوفة البيانات المالية مرتبة ومتوافقة مع التسميات القياسية المطلوبة
            return [
                'totalRevenue' => $totalRevenue,
                'totalRefundedPoints' => $totalRefundedPoints,
                'compensationPercentage' => $compensationPercentage,
                'systemTotalRevenue' => $systemTotalRevenue,
                'systemTotalRefundedPoints' => $systemTotalRefundedPoints,
                'systemCompensationPercentage' => $systemCompensationPercentage,
                'dailyLabels' => $dailyLabels,
                'dailyRevenue' => $dailyRevenueValues,
                'dailyCount' => $dailyCountValues,
            ];

        } catch (Exception $exception) {
            // توثيق الاستثناء لمتابعة الصيانة أو تصحيح الأخطاء لاحقاً
            Log::error('خطأ أثناء حساب التقرير المالي في FinancialReportService: ' . $exception->getMessage());
            throw $exception;
        }
    }
}
