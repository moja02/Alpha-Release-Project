<?php

namespace App\States\Booking;

use App\Models\Booking;
use Exception;

/**
 * صنف حالة الحجز المكتمل (Completed).
 * يمثل الحجز المنتهي بعد خروج السيارة من الموقف.
 */
class CompletedBookingState implements BookingState
{
    /**
     * الدخول غير مسموح لأن الحجز قد انتهى واكتمل.
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception دائماً
     */
    public function enter(Booking $booking): void
    {
        throw new Exception("الحجز مكتمل بالفعل ولا يمكن إعادة استخدامه!");
    }

    /**
     * الخروج غير مسموح لأن الحجز مكتمل بالفعل.
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception دائماً
     */
    public function exit(Booking $booking): void
    {
        throw new Exception("الحجز مكتمل بالفعل وقد سجلت السيارة خروجها سابقاً!");
    }

    /**
     * الإلغاء غير مسموح بعد اكتمال الحجز.
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception دائماً
     */
    public function cancel(Booking $booking): void
    {
        throw new Exception("الحجز مكتمل بالفعل ولا يمكن إلغاؤه!");
    }
}
