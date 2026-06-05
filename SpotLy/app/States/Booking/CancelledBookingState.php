<?php

namespace App\States\Booking;

use App\Models\Booking;
use Exception;

/**
 * صنف حالة الحجز الملغي (Cancelled).
 * يمثل الحجز الذي تم إلغاؤه إما تلقائياً أو من قبل المستخدم قبل الدخول.
 */
class CancelledBookingState implements BookingState
{
    /**
     * الدخول غير مسموح لأن الحجز قد تم إلغاؤه.
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception دائماً
     */
    public function enter(Booking $booking): void
    {
        throw new Exception("الحجز ملغي بالفعل ولا يمكن تسجيل دخول السيارة به!");
    }

    /**
     * الخروج غير مسموح لأن الحجز ملغي بالفعل.
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception دائماً
     */
    public function exit(Booking $booking): void
    {
        throw new Exception("الحجز ملغي بالفعل!");
    }

    /**
     * الإلغاء المتكرر لحجز ملغي غير مسموح.
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception دائماً
     */
    public function cancel(Booking $booking): void
    {
        throw new Exception("الحجز ملغي بالفعل سابقاً!");
    }
}
