<?php

namespace App\States\Booking;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * صنف حالة الحجز المؤكد (Confirmed).
 * يمثل الحجز بعد تأكيده وقبل وصول السيارة إلى الموقف الفعلي.
 */
class ConfirmedBookingState implements BookingState
{
    /**
     * تنفيذ الدخول للسيارة في حالة الحجز المؤكد.
     * يحول حالة الحجز إلى نشط (Active).
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception في حال فشل التخزين بقاعدة البيانات
     */
    public function enter(Booking $booking): void
    {
        try {
            // تحويل الحالة إلى نشطة عند دخول السيارة للموقف
            $booking->status = 'active';
            $booking->updated_at = now();
            $booking->save();
        } catch (Exception $exception) {
            // توثيق الاستثناء للمساعدة في الصيانة وتصحيح الأخطاء
            Log::error("فشل الانتقال إلى الحالة النشطة في ConfirmedBookingState: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * الخروج في حالة الحجز المؤكد غير مسموح لأن السيارة لم تدخل بعد.
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception دائماً لأن الدخول لم يتم
     */
    public function exit(Booking $booking): void
    {
        throw new Exception("لا يمكن تسجيل الخروج قبل الدخول إلى الموقف!");
    }

    /**
     * إلغاء حجز مؤكد وتغيير حالته إلى ملغي (Cancelled).
     *
     * @param Booking $booking كائن الحجز
     * @throws Exception في حال فشل التحديث في قاعدة البيانات
     */
    public function cancel(Booking $booking): void
    {
        try {
            // تحويل الحالة إلى ملغي
            $booking->status = 'cancelled';
            $booking->updated_at = now();
            $booking->save();
        } catch (Exception $exception) {
            // توثيق الخطأ
            Log::error("فشل إلغاء الحجز في ConfirmedBookingState: " . $exception->getMessage());
            throw $exception;
        }
    }
}
