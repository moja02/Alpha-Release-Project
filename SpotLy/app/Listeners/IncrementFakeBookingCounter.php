<?php

namespace App\Listeners;

use App\Events\BookingExpiredEvent;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * مراقب لزيادة عداد المخالفات (الحجوزات الوهمية) للمستخدم.
 */
class IncrementFakeBookingCounter
{
    /**
     * معالجة حدث انتهاء الحجز لزيادة عداد المخالفات.
     *
     * @param \App\Events\BookingExpiredEvent $event كائن الحدث
     * @return void
     * @throws Exception
     */
    public function handle($event): void
    {
        try {
            // جلب ملف المستخدم المرتبط بالحجز
            $user = $event->booking->user;
            if ($user) {
                // زيادة عداد المخالفات بمقدار 1 في قاعدة البيانات
                $user->increment('fake_booking_count');
                // تحديث كائن الموديل محلياً ليعكس القيمة الجديدة في قاعدة البيانات
                $user->refresh();
            }
        } catch (Exception $exception) {
            // توثيق الخطأ
            Log::error("خطأ أثناء زيادة عداد المخالفات في IncrementFakeBookingCounter: " . $exception->getMessage());
            throw $exception;
        }
    }
}
