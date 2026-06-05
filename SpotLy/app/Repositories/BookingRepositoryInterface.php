<?php

namespace App\Repositories;

/**
 * واجهة مستودع الحجوزات لتجريد عمليات الوصول للبيانات.
 */
interface BookingRepositoryInterface
{
    /**
     * جلب الحجز النشط المؤكد للمستخدم مع معلومات موقف السيارات.
     *
     * @param int $userId معرف المستخدم
     * @return mixed كائن الحجز أو null
     */
    public function getActiveBookingForUser(int $userId);

    /**
     * التحقق من وجود حجز مؤكد نشط للمستخدم (منع الحجز المزدوج).
     *
     * @param int $userId معرف المستخدم
     * @param bool $sharedLock تفعيل القفل المشترك للمزامنة
     * @return bool true إذا كان لديه حجز نشط
     */
    public function hasActiveBookingForUser(int $userId, bool $sharedLock = false): bool;

    /**
     * جلب الحجز باستخدام معرفه الفريد.
     *
     * @param int $bookingId معرف الحجز
     * @param bool $lockForUpdate تفعيل قفل التحديث
     * @return mixed كائن الحجز أو null
     */
    public function getById(int $bookingId, bool $lockForUpdate = false);

    /**
     * جلب كل الحجوزات المبدئية المؤكدة التي انتهت صلاحيتها.
     *
     * @param \DateTimeInterface $currentTime الوقت الحالي للمقارنة
     * @param bool $lockForUpdate تفعيل قفل التحديث
     * @return \Illuminate\Database\Eloquent\Collection قائمة الحجوزات منتهية الصلاحية
     */
    public function getExpiredInitialBookings(\DateTimeInterface $currentTime, bool $lockForUpdate = false);

    /**
     * إنشاء حجز جديد في قاعدة البيانات.
     *
     * @param array $data بيانات الحجز
     * @return int معرف الحجز المنشأ حديثاً
     */
    public function createBooking(array $data): int;

    /**
     * تحديث بيانات حجز معين.
     *
     * @param int $bookingId معرف الحجز
     * @param array $data البيانات المحدثة
     * @return bool نجاح أو فشل التحديث
     */
    public function updateBooking(int $bookingId, array $data): bool;

    /**
     * التحقق من وجود سيارة باللوحة المحددة داخل الموقف حالياً بحالة نشطة.
     *
     * @param string $plateNumber رقم لوحة السيارة
     * @return bool
     */
    public function getActiveBookingByPlate(string $plateNumber): bool;

    /**
     * جلب الحجز المؤكد غير النشط بعد للوحة محددة.
     *
     * @param string $plateNumber رقم لوحة السيارة
     * @return mixed كائن الحجز أو null
     */
    public function getConfirmedBookingByPlate(string $plateNumber);

    /**
     * جلب الحجز النشط لسيارة بلوحة معينة داخل ساحة موقف محددة.
     *
     * @param string $plateNumber رقم لوحة السيارة
     * @param int $parkingId معرف الموقف
     * @return mixed كائن الحجز أو null
     */
    public function getActiveBookingInParking(string $plateNumber, int $parkingId);
}
