<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * فئة مستودع التقارير الفعلي للتعامل مع عمليات الوصول والاستعلام من قاعدة البيانات.
 */
class ReportRepository implements ReportRepositoryInterface
{
    /**
     * حساب إجمالي الإيرادات لساحة معينة أو للنظام بأكمله خلال فترة زمنية محددة.
     *
     * @param int|null $parkingId معرف ساحة موقف السيارات
     * @param string|null $startDate تاريخ بداية الفترة للتقرير
     * @param string|null $endDate تاريخ نهاية الفترة للتقرير
     * @return float إجمالي نقاط شحن الرصيد المعتمدة
     * @throws Exception
     */
    public function calculateRevenue(?int $parkingId = null, ?string $startDate = null, ?string $endDate = null): float
    {
        try {
            // إنشاء استعلام على جدول طلبات شحن الرصيد المعتمدة
            $queryBuilder = DB::table('recharge_requests')
                ->where('status', 'Approved');

            // تصفية الاستعلام حسب الساحة المحددة إذا تم تمريرها
            if ($parkingId !== null) {
                $queryBuilder->where('parking_id', $parkingId);
            }

            // تصفية الاستعلام حسب تاريخ البداية للفترة الزمنية
            if ($startDate !== null) {
                $queryBuilder->where('created_at', '>=', $startDate);
            }

            // تصفية الاستعلام حسب تاريخ النهاية للفترة الزمنية
            if ($endDate !== null) {
                $queryBuilder->where('created_at', '<=', $endDate);
            }

            // حساب المجموع الكلي للنقاط المطلوبة وإرجاعها كقيمة عشرية
            $totalRevenue = (float) $queryBuilder->sum('requested_points');

            return $totalRevenue;
        } catch (Exception $exception) {
            // تسجيل الخطأ بالتفصيل لمساعدة المطورين في الصيانة
            Log::error("خطأ أثناء حساب الإيرادات في ReportRepository: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * حساب إجمالي عدد المخالفات (الحجوزات الملغاة أو التلقائية) لساحة معينة أو للنظام بأكمله.
     *
     * @param int|null $parkingId معرف ساحة موقف السيارات
     * @param string|null $startDate تاريخ بداية الفترة للتقرير
     * @param string|null $endDate تاريخ نهاية الفترة للتقرير
     * @return int إجمالي عدد المخالفات المسجلة
     * @throws Exception
     */
    public function calculateViolations(?int $parkingId = null, ?string $startDate = null, ?string $endDate = null): int
    {
        try {
            // إنشاء استعلام على جدول الحجوزات لتصفية الحجوزات الملغاة
            $queryBuilder = DB::table('bookings')
                ->whereIn('status', ['cancelled', 'auto_cancelled']);

            // تصفية الاستعلام حسب الساحة المحددة إذا تم تمريرها
            if ($parkingId !== null) {
                $queryBuilder->where('parking_id', $parkingId);
            }

            // تصفية الاستعلام حسب تاريخ البداية للفترة الزمنية
            if ($startDate !== null) {
                $queryBuilder->where('created_at', '>=', $startDate);
            }

            // تصفية الاستعلام حسب تاريخ النهاية للفترة الزمنية
            if ($endDate !== null) {
                $queryBuilder->where('created_at', '<=', $endDate);
            }

            // حساب إجمالي عدد السجلات المطابقة للمخالفات
            $violationsCount = (int) $queryBuilder->count();

            return $violationsCount;
        } catch (Exception $exception) {
            // تسجيل الخطأ بالتفصيل لمساعدة المطورين في الصيانة
            Log::error("خطأ أثناء حساب المخالفات في ReportRepository: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * حساب ساعات الذروة وترتيبها تنازلياً بناءً على عدد الحجوزات لساحة معينة أو للنظام بأكمله.
     *
     * @param int|null $parkingId معرف ساحة موقف السيارات
     * @param string|null $startDate تاريخ بداية الفترة للتقرير
     * @param string|null $endDate تاريخ نهاية الفترة للتقرير
     * @return array قائمة الساعات مرتبة تنازلياً حسب مستوى الإشغال والنشاط
     * @throws Exception
     */
    public function calculatePeakHours(?int $parkingId = null, ?string $startDate = null, ?string $endDate = null): array
    {
        try {
            // تحديد محرك قاعدة البيانات المستخدم لتفادي أخطاء التوافقية بين SQLite و MySQL
            $databaseDriver = DB::connection()->getDriverName();
            $hourExpression = "";

            if ($databaseDriver === 'sqlite') {
                // استخراج الساعة في بيئة SQLite المحلية
                $hourExpression = "strftime('%H', start_time)";
            } else {
                // استخراج الساعة في بيئة MySQL الإنتاجية
                $hourExpression = "HOUR(start_time)";
            }

            // إعداد الاستعلام الأساسي للحجوزات لتجميع الساعات وحساب التكرار
            $queryBuilder = DB::table('bookings')
                ->select(DB::raw("{$hourExpression} as booking_hour"), DB::raw("COUNT(id) as booking_count"));

            // تصفية الاستعلام حسب الساحة المحددة إذا تم تمريرها
            if ($parkingId !== null) {
                $queryBuilder->where('parking_id', $parkingId);
            }

            // تصفية الاستعلام حسب تاريخ البداية للفترة الزمنية
            if ($startDate !== null) {
                $queryBuilder->where('start_time', '>=', $startDate);
            }

            // تصفية الاستعلام حسب تاريخ النهاية للفترة الزمنية
            if ($endDate !== null) {
                $queryBuilder->where('start_time', '<=', $endDate);
            }

            // تجميع السجلات حسب الساعة وترتيب النتائج تنازلياً بناءً على عدد الحجوزات
            $peakHoursResult = $queryBuilder->groupBy('booking_hour')
                ->orderBy('booking_count', 'desc')
                ->get()
                ->toArray();

            // تحويل النتيجة إلى مصفوفة بسيطة مناسبة للعرض والتصدير
            $peakHoursList = array_map(function ($row) {
                return [
                    'hour' => (int) $row->booking_hour,
                    'count' => (int) $row->booking_count
                ];
            }, $peakHoursResult);

            return $peakHoursList;
        } catch (Exception $exception) {
            // تسجيل الخطأ بالتفصيل لمساعدة المطورين في الصيانة
            Log::error("خطأ أثناء حساب ساعات الذروة في ReportRepository: " . $exception->getMessage());
            throw $exception;
        }
    }
}
