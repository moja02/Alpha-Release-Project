<?php

namespace App\Repositories;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * تطبيق مستودع الحجوزات باستخدام نماذج Eloquent للتعامل مع قاعدة البيانات.
 */
class BookingRepository implements BookingRepositoryInterface
{
    /**
     * جلب الحجز النشط المؤكد للمستخدم مع معلومات موقف السيارات.
     *
     * @param int $userId معرف المستخدم
     * @return mixed كائن الحجز أو null
     * @throws Exception
     */
    public function getActiveBookingForUser(int $userId)
    {
        try {
            // استخدام Eloquent لجلب الحجز مع الربط بجدول مواقف السيارات للحصول على اسم الموقف
            return Booking::join('parkings', 'bookings.parking_id', '=', 'parkings.id')
                ->where('bookings.user_id', $userId)
                ->where('bookings.status', 'confirmed') 
                ->select('bookings.*', 'parkings.name as parking_name')
                ->orderBy('bookings.id', 'desc')
                ->first();
        } catch (Exception $exception) {
            Log::error("خطأ في getActiveBookingForUser: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * التحقق من وجود حجز مؤكد نشط للمستخدم (منع الحجز المزدوج).
     *
     * @param int $userId معرف المستخدم
     * @param bool $sharedLock تفعيل القفل المشترك للمزامنة
     * @return bool true إذا كان لديه حجز نشط
     * @throws Exception
     */
    public function hasActiveBookingForUser(int $userId, bool $sharedLock = false): bool
    {
        try {
            // إعداد الاستعلام لفحص وجود حجز نشط ومؤكد
            $query = Booking::where('user_id', $userId)
                ->where('status', 'confirmed');

            // تطبيق القفل المشترك لضمان سلامة العمليات المتزامنة في المعاملات المالية
            if ($sharedLock) {
                $query->sharedLock();
            }

            return $query->exists();
        } catch (Exception $exception) {
            Log::error("خطأ في hasActiveBookingForUser: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * جلب الحجز باستخدام معرفه الفريد.
     *
     * @param int $bookingId معرف الحجز
     * @param bool $lockForUpdate تفعيل قفل التحديث
     * @return mixed كائن الحجز أو null
     * @throws Exception
     */
    public function getById(int $bookingId, bool $lockForUpdate = false)
    {
        try {
            $query = Booking::where('id', $bookingId);

            // تطبيق قفل التحديث لمنع التعديل المتزامن
            if ($lockForUpdate) {
                $query->lockForUpdate();
            }

            return $query->first();
        } catch (Exception $exception) {
            Log::error("خطأ في getById: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * جلب كل الحجوزات المبدئية المؤكدة التي انتهت صلاحيتها.
     *
     * @param \DateTimeInterface $currentTime الوقت الحالي للمقارنة
     * @param bool $lockForUpdate تفعيل قفل التحديث
     * @return \Illuminate\Database\Eloquent\Collection قائمة الحجوزات منتهية الصلاحية
     * @throws Exception
     */
    public function getExpiredInitialBookings(\DateTimeInterface $currentTime, bool $lockForUpdate = false)
    {
        try {
            $query = Booking::where('is_guest', false)
                ->where('type', 'initial')
                ->where('status', 'confirmed')
                ->where('end_time', '<=', $currentTime);

            // تطبيق قفل التحديث لضمان حصرية المعالجة في حلقة التنظيف
            if ($lockForUpdate) {
                $query->lockForUpdate();
            }

            return $query->get();
        } catch (Exception $exception) {
            Log::error("خطأ في getExpiredInitialBookings: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * إنشاء حجز جديد في قاعدة البيانات.
     *
     * @param array $data بيانات الحجز
     * @return int معرف الحجز المنشأ حديثاً
     * @throws Exception
     */
    public function createBooking(array $data): int
    {
        try {
            // إضافة طوابع زمنية يدوية أو الاعتماد على الحفظ الكائني
            $booking = Booking::create($data);
            return $booking->id;
        } catch (Exception $exception) {
            Log::error("خطأ في createBooking: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * تحديث بيانات حجز معين.
     *
     * @param int $bookingId معرف الحجز
     * @param array $data البيانات المحدثة
     * @return bool نجاح أو فشل التحديث
     * @throws Exception
     */
    public function updateBooking(int $bookingId, array $data): bool
    {
        try {
            $booking = Booking::find($bookingId);
            if ($booking) {
                return $booking->update($data);
            }
            return false;
        } catch (Exception $exception) {
            Log::error("خطأ في updateBooking: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * التحقق من وجود سيارة باللوحة المحددة داخل الموقف حالياً بحالة نشطة.
     *
     * @param string $plateNumber رقم لوحة السيارة
     * @return bool
     * @throws Exception
     */
    public function getActiveBookingByPlate(string $plateNumber): bool
    {
        try {
            return Booking::where('plate_number', $plateNumber)
                ->where('status', 'active')
                ->exists();
        } catch (Exception $exception) {
            Log::error("خطأ في getActiveBookingByPlate: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * جلب الحجز المؤكد غير النشط بعد للوحة محددة.
     *
     * @param string $plateNumber رقم لوحة السيارة
     * @return mixed كائن الحجز أو null
     * @throws Exception
     */
    public function getConfirmedBookingByPlate(string $plateNumber)
    {
        try {
            return Booking::where('plate_number', $plateNumber)
                ->where('status', 'confirmed')
                ->first();
        } catch (Exception $exception) {
            Log::error("خطأ في getConfirmedBookingByPlate: " . $exception->getMessage());
            throw $exception;
        }
    }

    /**
     * جلب الحجز النشط لسيارة بلوحة معينة داخل ساحة موقف محددة.
     *
     * @param string $plateNumber رقم لوحة السيارة
     * @param int $parkingId معرف الموقف
     * @return mixed كائن الحجز أو null
     * @throws Exception
     */
    public function getActiveBookingInParking(string $plateNumber, int $parkingId)
    {
        try {
            return Booking::where('plate_number', $plateNumber)
                ->where('status', 'active')
                ->where('parking_id', $parkingId)
                ->first();
        } catch (Exception $exception) {
            Log::error("خطأ في getActiveBookingInParking: " . $exception->getMessage());
            throw $exception;
        }
    }
}
