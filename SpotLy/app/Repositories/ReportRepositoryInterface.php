<?php

namespace App\Repositories;

/**
 * واجهة مستودع التقارير لتحديد العمليات المطلوبة لحساب الإحصائيات المختلفة.
 */
interface ReportRepositoryInterface
{
    /**
     * حساب إجمالي الإيرادات لساحة معينة أو للنظام بأكمله خلال فترة زمنية محددة.
     *
     * @param int|null $parkingId معرف ساحة موقف السيارات (إذا كان فارغاً يتم الحساب للنظام ككل)
     * @param string|null $startDate تاريخ بداية الفترة للتقرير (مثال: 2026-06-01)
     * @param string|null $endDate تاريخ نهاية الفترة للتقرير (مثال: 2026-06-30)
     * @return float إجمالي نقاط شحن الرصيد المعتمدة
     */
    public function calculateRevenue(?int $parkingId = null, ?string $startDate = null, ?string $endDate = null): float;

    /**
     * حساب إجمالي عدد المخالفات (الحجوزات الملغاة أو التلقائية) لساحة معينة أو للنظام بأكمله.
     *
     * @param int|null $parkingId معرف ساحة موقف السيارات (إذا كان فارغاً يتم الحساب للنظام ككل)
     * @param string|null $startDate تاريخ بداية الفترة للتقرير
     * @param string|null $endDate تاريخ نهاية الفترة للتقرير
     * @return int إجمالي عدد المخالفات المسجلة
     */
    public function calculateViolations(?int $parkingId = null, ?string $startDate = null, ?string $endDate = null): int;

    /**
     * حساب ساعات الذروة وترتيبها تنازلياً بناءً على عدد الحجوزات لساحة معينة أو للنظام بأكمله.
     *
     * @param int|null $parkingId معرف ساحة موقف السيارات (إذا كان فارغاً يتم الحساب للنظام ككل)
     * @param string|null $startDate تاريخ بداية الفترة للتقرير
     * @param string|null $endDate تاريخ نهاية الفترة للتقرير
     * @return array قائمة الساعات مرتبة تنازلياً حسب مستوى الإشغال والنشاط
     */
    public function calculatePeakHours(?int $parkingId = null, ?string $startDate = null, ?string $endDate = null): array;
}
