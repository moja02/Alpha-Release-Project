<?php

namespace App\States\Booking;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * صنف حالة الحجز النشط (Active).
 * يمثل الحجز والسيارة متواجدة فعلياً داخل موقف السيارات.
 */
class ActiveBookingState implements BookingState
{
    /**
     * الدخول مرة أخرى في حالة الحجز النشط غير مسموح.
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception دائماً لأن السيارة متواجدة بالفعل بالداخل
     */
    public function enter(Booking $booking): void
    {
        throw new Exception("السيارة داخل الموقف بالفعل!");
    }

    /**
     * تسجيل خروج السيارة وتغيير حالة الحجز إلى مكتمل (Completed).
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception في حال فشل التحديث في قاعدة البيانات
     */
    public function exit(Booking $booking): void
    {
        try {
            // تحويل الحالة إلى مكتمل وتسجيل وقت الخروج الحالي
            $booking->status = 'completed';
            $booking->end_time = now();
            $booking->updated_at = now();
            $booking->save();
        } catch (Exception $exception) {
            // توثيق الخطأ لتسهيل تتبع الأعطال
            Log::error("فشل تسجيل الخروج في ActiveBookingState: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * الإلغاء غير مسموح طالما أن السيارة متواجدة داخل الموقف.
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception دائماً لمنع إلغاء الحجوزات النشطة
     */
    public function cancel(Booking $booking): void
    {
        throw new Exception("لا يمكن إلغاء الحجز والسيارة داخل الموقف!");
    }
}
