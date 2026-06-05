<?php

namespace App\States\Booking;

use App\Models\Booking;

/**
 * واجهة حالة الحجز (BookingState).
 * تحدد هذه الواجهة الانتقالات البرمجية المتاحة لحالات الحجز المختلفة.
 */
interface BookingState
{
    /**
     * معالجة دخول السيارة إلى موقف السيارات.
     * @param Booking $booking كائن الحجز المراد تغيير حالته
     * @return void
     */
    public function enter(Booking $booking): void;

    /**
     * معالجة خروج السيارة من موقف السيارات.
     * @param Booking $booking كائن الحجز المراد تغيير حالته
     * @return void
     */
    public function exit(Booking $booking): void;

    /**
     * معالجة إلغاء الحجز من قبل المستخدم.
     * @param Booking $booking كائن الحجز المراد تغيير حالته
     * @return void
     */
    public function cancel(Booking $booking): void;
}
