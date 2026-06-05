<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    // السماح بالإضافة في كل الحقول
    protected $guarded = [];

    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'account_id');
    }

    public function parking()
    {
        return $this->belongsTo(Parking::class);
    }

    /**
     * جلب كائن حالة الحجز الحالي بناءً على القيمة المخزنة بحقل الحالة في قاعدة البيانات.
     *
     * @return \App\States\Booking\BookingState كائن يمثل حالة الحجز الحالية
     * @throws \Exception إذا كانت الحالة غير معرفة بالنظام
     */
    public function getState(): \App\States\Booking\BookingState
    {
        try {
            switch ($this->status) {
                case 'confirmed':
                    return new \App\States\Booking\ConfirmedBookingState();
                case 'active':
                    return new \App\States\Booking\ActiveBookingState();
                case 'completed':
                    return new \App\States\Booking\CompletedBookingState();
                case 'cancelled':
                    return new \App\States\Booking\CancelledBookingState();
                default:
                    throw new \Exception("حالة حجز غير معروفة: " . $this->status);
            }
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error("خطأ في جلب كائن حالة الحجز: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * تفويض عملية دخول الموقف للسيارة إلى كائن الحالة الحالية.
     *
     * @return void
     * @throws \Exception
     */
    public function enter(): void
    {
        try {
            $this->getState()->enter($this);
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error("خطأ أثناء الدخول للموقف: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * تفويض عملية خروج السيارة من الموقف إلى كائن الحالة الحالية.
     *
     * @return void
     * @throws \Exception
     */
    public function exitParking(): void
    {
        try {
            $this->getState()->exit($this);
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error("خطأ أثناء الخروج من الموقف: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * تفويض عملية إلغاء الحجز إلى كائن الحالة الحالية.
     *
     * @return void
     * @throws \Exception
     */
    public function cancelBooking(): void
    {
        try {
            $this->getState()->cancel($this);
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error("خطأ أثناء إلغاء الحجز: " . $exception->getMessage());
            throw $exception;
        }
    }
}
