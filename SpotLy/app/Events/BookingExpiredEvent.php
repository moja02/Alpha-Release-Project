<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * صنف الحدث لحالة حجز منته الصلاحية.
 */
class BookingExpiredEvent
{
    use Dispatchable, SerializesModels;

    /**
     * @var Booking كائن الحجز منته الصلاحية
     */
    public $booking;

    /**
     * إنشاء كائن حدث جديد وتمرير الحجز منته الصلاحية له.
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception
     */
    public function __construct(Booking $booking)
    {
        try {
            // حفظ كائن الحجز في الخاصية العامة
            $this->booking = $booking;
        } catch (Exception $exception) {
            // توثيق الخطأ
            Log::error("خطأ أثناء إنشاء حدث انتهاء صلاحية الحجز: " . $exception->getMessage());
            throw $exception;
        }
    }
}
