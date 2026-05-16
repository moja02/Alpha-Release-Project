<?php

namespace App\Http\Controllers;

use App\Models\Parking;
use App\Models\Booking;
use App\Models\Wallet;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SpotlyNotificationMail;

class BookingController extends Controller
{
     
     // جلب كافة ساحات الوقوف وسعتها المتاحة

    public function getSpots()
    {
        try {
            // استخدام Left Join لضمان ظهور الساحة حتى لو لم يتم تعيين موظف لها بعد
            $parkingAreas = DB::table('parkings')
                ->leftJoin('employees', 'parkings.employee_id', '=', 'employees.id')
                ->select('parkings.*', 'employees.bank_account_number as employee_bank_account')
                ->orderBy('parkings.id')
                ->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $parkingAreas
            ], 200);
        } catch (\Exception $exception) {
            Log::error('Error fetching parking areas: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
    
     // جلب بيانات التذكرة النشطة للسائق (الحجز المبدئي أو الفعلي)
     
    public function getActiveBooking(Request $request)
    {
        try {
            $userId = $request->input('userId');

            //  جلب الحجز "المؤكد" فقط
            $activeBooking = \Illuminate\Support\Facades\DB::table('bookings')
                ->join('parkings', 'bookings.parking_id', '=', 'parkings.id')
                ->where('bookings.user_id', $userId)
                ->where('bookings.status', 'confirmed') 
                ->select('bookings.*', 'parkings.name as parking_name')
                ->orderBy('bookings.id', 'desc')
                ->first();

            if ($activeBooking) {
                return response()->json([
                    'status' => 'success',
                    'hasActiveBooking' => true,
                    'bookingData' => $activeBooking
                ], 200);
            } else {
                return response()->json([
                    'status' => 'success',
                    'hasActiveBooking' => false // إذا كان ملغياً أو غير موجود، سيرجع false
                ], 200);
            }

        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
    
    /**
     * معالجة واعتماد حجز جديد بناءً على السعة المتاحة
     */
    public function createBooking(Request $request)
    {
        try {
            // 1. التحقق من المدخلات الجديدة بناءً على نوع الحجز
            $request->validate([
                'userId' => 'required|integer',
                'parkingId' => 'required|integer',
                'bookingType' => 'required|in:initial,actual',
                'startTime' => 'required_if:bookingType,actual|date',
                'endTime' => 'required_if:bookingType,actual|date|after:startTime',
            ]);

            $inputUserId = $request->input('userId');
            $inputParkingId = $request->input('parkingId');
            $inputType = $request->input('bookingType');

            \Illuminate\Support\Facades\DB::beginTransaction();

            // 2. منع الحجز المزدوج
            $hasExistingBooking = \Illuminate\Support\Facades\DB::table('bookings')
                ->where('user_id', $inputUserId)
                ->where('status', 'confirmed')
                ->sharedLock()
                ->exists();

            if ($hasExistingBooking) {
                return response()->json(['status' => 'error', 'message' => 'لديك حجز نشط بالفعل.'], 400);
            }

            // 3. التحقق من السعة المتاحة (يجب أن تكون أكبر من 0)
            $targetParkingArea = \Illuminate\Support\Facades\DB::table('parkings')
                ->where('id', $inputParkingId)->lockForUpdate()->first();

            if (!$targetParkingArea || $targetParkingArea->available_capacity <= 0) {
                return response()->json(['status' => 'error', 'message' => 'عذراً، هذه الساحة ممتلئة بالكامل.'], 400);
            }

            // جلب رقم اللوحة
            $driverProfile = \Illuminate\Support\Facades\DB::table('users')->where('account_id', $inputUserId)->first();
            $driverPlateNumber = $driverProfile ? $driverProfile->plate_number : 'غير محدد';

            // 4. معالجة الأوقات والخصم المالي بناءً على السيناريو الخاص بك
            if ($inputType === 'initial') {
                // الحجز المبدئي: يبدأ الآن وينتهي بعد 30 دقيقة (المهلة)
                $startTime = now();
                $endTime = now()->addMinutes(30);
                $notificationMsg = "تم إنشاء حجز مبدئي. أمامك 30 دقيقة للوصول للموقف.";
            } else {
                // الحجز الفعلي: الاعتماد على الأوقات المدخلة من السائق
                $startTime = \Carbon\Carbon::parse($request->input('startTime'));
                $endTime = \Carbon\Carbon::parse($request->input('endTime'));
                
                // حساب عدد الساعات ( تكلفة الساعة 2.5 نقاط)
                $hoursDifference = $startTime->diffInHours($endTime);
                $totalHours = $hoursDifference > 0 ? $hoursDifference : 1; // كحد أدنى ساعة واحدة
                $bookingCost = $totalHours * 2.5;

                // التحقق من الرصيد والخصم
                $userWallet = \Illuminate\Support\Facades\DB::table('wallets')->where('user_id', $inputUserId)->lockForUpdate()->first();
                if (!$userWallet || $userWallet->balance < $bookingCost) {
                    return response()->json(['status' => 'error', 'message' => "رصيدك غير كافٍ. تكلفة الحجز {$bookingCost} نقطة."], 400);
                }
                
                \Illuminate\Support\Facades\DB::table('wallets')->where('user_id', $inputUserId)->decrement('balance', $bookingCost);
                $notificationMsg = "تم تأكيد حجزك الفعلي وخصم {$bookingCost} نقطة من محفظتك.";
            }

            // 5. إنقاص مكان واحد من الساحة المشغولة
            \Illuminate\Support\Facades\DB::table('parkings')->where('id', $inputParkingId)->decrement('available_capacity', 1);

            // 6. إدراج الحجز في قاعدة البيانات
            $insertedBookingId = \Illuminate\Support\Facades\DB::table('bookings')->insertGetId([
                'user_id' => $inputUserId,
                'parking_id' => $inputParkingId,
                'plate_number' => $driverPlateNumber,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'type' => $inputType,
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // إرسال الإشعار
            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'user_id' => $inputUserId,
                'message' => $notificationMsg,
                'type' => 'Booking_Confirmed',
                'created_at' => now()
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json(['status' => 'success', 'bookingId' => $insertedBookingId], 201);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    /**
     * FR3 - تنفيذ شروط الإلغاء واسترجاع النقاط
     */
    public function cancelBooking(Request $request)
    {
        try {
            $request->validate(['bookingId' => 'required|integer']);
            $targetBookingId = $request->input('bookingId');

            \Illuminate\Support\Facades\DB::beginTransaction();

            $bookingRecord = \Illuminate\Support\Facades\DB::table('bookings')
                ->where('id', $targetBookingId)
                ->lockForUpdate()
                ->first();

            if (!$bookingRecord || $bookingRecord->status !== 'confirmed') {
                return response()->json(['status' => 'error', 'message' => 'الحجز غير موجود أو ملغي مسبقاً.'], 400);
            }

            $currentTime = now();
            $bookingStartTime = \Carbon\Carbon::parse($bookingRecord->start_time);
            $bookingEndTime = \Carbon\Carbon::parse($bookingRecord->end_time);
            
            // 🔴 التحديث الجوهري: فصل منطق الوقت بناءً على نوع الحجز
            if ($bookingRecord->type === 'actual') {
                // للحجز الفعلي: نمنع الإلغاء إذا حل وقت البداية
                if ($currentTime->greaterThanOrEqualTo($bookingStartTime)) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'عذراً، لا يمكن إلغاء الحجز الفعلي بعد حلول موعد البداية.'
                    ], 403);
                }
            } else {
                // للحجز المبدئي: نمنع الإلغاء إذا انتهت مهلة الـ 20 دقيقة (ستعالج كـ Fake Booking لاحقاً)
                if ($currentTime->greaterThanOrEqualTo($bookingEndTime)) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'عذراً، انتهت مهلة الحجز المبدئي (20 دقيقة).'
                    ], 403);
                }
            }

            $refundPercentage = 0;
            $refundAmount = 0;

            // حساب الاسترجاع المالي للحجز الفعلي فقط (لأن المبدئي لم يخصم منه نقاط)
            if ($bookingRecord->type === 'actual') {
                $minutesToStart = $currentTime->diffInMinutes($bookingStartTime, false);
                
                if ($minutesToStart > 30) {
                    $refundPercentage = 100;
                } else {
                    $refundPercentage = 50;
                }

                $totalHours = $bookingStartTime->diffInHours(\Carbon\Carbon::parse($bookingRecord->end_time)) ?: 1;
                $originalCost = $totalHours * 2.5; // تسعيرة الساعة 2.5
                $refundAmount = ($originalCost * $refundPercentage) / 100;

                \Illuminate\Support\Facades\DB::table('wallets')
                    ->where('user_id', $bookingRecord->user_id)
                    ->increment('balance', $refundAmount);
            }

            // تحديث حالة الحجز وزيادة السعة المتاحة في الساحة
            \Illuminate\Support\Facades\DB::table('bookings')
                ->where('id', $targetBookingId)
                ->update(['status' => 'cancelled', 'updated_at' => now()]);

            \Illuminate\Support\Facades\DB::table('parkings')
                ->where('id', $bookingRecord->parking_id)
                ->increment('available_capacity', 1);

            // إرسال إشعار للمستخدم
            $notificationMsg = $bookingRecord->type === 'actual' 
                ? "تم إلغاء الحجز بنجاح. تم استرجاع {$refundAmount} نقطة ({$refundPercentage}%) لمحفظتك."
                : "تم إلغاء الحجز المبدئي بنجاح.";

            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'user_id' => $bookingRecord->user_id,
                'message' => $notificationMsg,
                'type' => 'Booking_Cancelled',
                'created_at' => now()
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $notificationMsg
            ], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
    /**
     * تعديل الموقف وتبديله بموقف آخر
     */
    public function changeSpot(Request $request)
    {
        try {
            $request->validate([
                'bookingId' => 'required|integer',
                'newParkingId' => 'required|integer|exists:parkings,id'
            ]);

            $bookingId = $request->input('bookingId');
            $newParkingId = $request->input('newParkingId');

            \Illuminate\Support\Facades\DB::beginTransaction();

            $oldBooking = \Illuminate\Support\Facades\DB::table('bookings')->where('id', $bookingId)->lockForUpdate()->first();
            $currentTime = now();
            
            //  فصل منطق الوقت للتبديل أيضاً
            if ($oldBooking->type === 'actual') {
                if ($currentTime->greaterThanOrEqualTo(\Carbon\Carbon::parse($oldBooking->start_time))) {
                    return response()->json(['status' => 'error', 'message' => 'لا يمكن تغيير الموقف بعد بدء وقت الحجز الفعلي.'], 403);
                }
            } else {
                if ($currentTime->greaterThanOrEqualTo(\Carbon\Carbon::parse($oldBooking->end_time))) {
                    return response()->json(['status' => 'error', 'message' => 'لا يمكن تغيير الموقف لأن مهلة الحجز المبدئي قد انتهت.'], 403);
                }
            }

            // فحص سعة الموقف الجديد
            $newParking = \Illuminate\Support\Facades\DB::table('parkings')->where('id', $newParkingId)->lockForUpdate()->first();
            if ($newParking->available_capacity <= 0) {
                return response()->json(['status' => 'error', 'message' => 'عذراً، الساحة الجديدة ممتلئة بالكامل.'], 400);
            }

            // تنفيذ التبديل وإعادة السعات
            \Illuminate\Support\Facades\DB::table('parkings')->where('id', $oldBooking->parking_id)->increment('available_capacity', 1);
            \Illuminate\Support\Facades\DB::table('parkings')->where('id', $newParkingId)->decrement('available_capacity', 1);

            \Illuminate\Support\Facades\DB::table('bookings')
                ->where('id', $bookingId)
                ->update(['parking_id' => $newParkingId, 'updated_at' => now()]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json(['status' => 'success', 'message' => 'تم تبديل الساحة بنجاح.'], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    public function cleanupExpiredBookings()
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $currentTime = now();

            // 1. جلب كل الحجوزات المبدئية المؤكدة التي انتهت مهلة الـ 20 دقيقة الخاصة بها
            $expiredBookings = \Illuminate\Support\Facades\DB::table('bookings')
                ->where('type', 'initial')
                ->where('status', 'confirmed')
                ->where('end_time', '<=', $currentTime)
                ->lockForUpdate()
                ->get();

            $processedCount = 0;

            foreach ($expiredBookings as $booking) {
                // أ. تغيير حالة الحجز إلى 'cancelled'
                \Illuminate\Support\Facades\DB::table('bookings')
                    ->where('id', $booking->id)
                    ->update(['status' => 'cancelled', 'updated_at' => $currentTime]);

                // ب. إرجاع السعة للساحة
                \Illuminate\Support\Facades\DB::table('parkings')
                    ->where('id', $booking->parking_id)
                    ->increment('available_capacity', 1);

                // ج. زيادة عداد المخالفات للسائق
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('account_id', $booking->user_id)
                    ->increment('fake_booking_count', 1);

                // د. جلب بيانات السائق والحساب (للحصول على البريد الإلكتروني)
                $driver = \Illuminate\Support\Facades\DB::table('users')
                    ->where('account_id', $booking->user_id)
                    ->first();

                $account = \Illuminate\Support\Facades\DB::table('accounts')
                    ->where('id', $booking->user_id)
                    ->first();

                // تجهيز الإيميل (إذا كان موجوداً)
                $targetEmail = ($account && isset($account->email)) ? $account->email : null;

                // هـ. التحقق من حالة الحظر وإرسال الإشعارات
                if ($driver && $driver->fake_booking_count >= 3) {
                    // تحديث حالة السائق إلى محظور
                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('account_id', $booking->user_id)
                        ->update(['status' => 'blocked']);

                    // إدخال الإشعار في قاعدة البيانات مع حفظ الإيميل
                    \Illuminate\Support\Facades\DB::table('notifications')->insert([
                        'user_id' => $booking->user_id,
                        'message' => 'تم حظر حسابك لتجاوز الحد الأقصى للمخالفات (3 مرات حجز وهمي دون حضور).',
                        'type' => 'Account_Blocked',
                        'sent_to_email' => $targetEmail, 
                        'created_at' => $currentTime
                    ]);

                    // إرسال الإيميل الفوري
                    if ($targetEmail) {
                        $mailData = [
                            'title' => 'تنبيه إداري: تم حظر حسابك 🚫',
                            'body' => 'نعلمك بأنه تم حظر حسابك في نظام SpotLy لتجاوزك الحد الأقصى من المخالفات (3 مرات حجز مبدئي دون الحضور). يرجى مراجعة إدارة المواقف.'
                        ];
                        \Illuminate\Support\Facades\Mail::to($targetEmail)->send(new \App\Mail\SpotlyNotificationMail($mailData));
                    }
                } else {
                    // إدخال الإشعار العادي في قاعدة البيانات مع حفظ الإيميل
                    \Illuminate\Support\Facades\DB::table('notifications')->insert([
                        'user_id' => $booking->user_id,
                        'message' => 'انتهت مهلة الحجز المبدئي (20 دقيقة) دون حضورك. تم إلغاء الحجز وتسجيل مخالفة في سجلك.',
                        'type' => 'Booking_Expired',
                        'sent_to_email' => $targetEmail, 
                        'created_at' => $currentTime
                    ]);

                    // إرسال الإيميل الفوري
                    if ($targetEmail) {
                        $mailData = [
                            'title' => 'إشعار تسجيل مخالفة حجز وهمي ⚠️',
                            'body' => "لقد انتهت مهلة الحجز المبدئي الخاصة بك دون تأكيد حضورك. تم تسجيل مخالفة في سجلك. نذكرك بأنه عند الوصول لـ 3 مخالفات سيتم حظر الحساب تلقائياً."
                        ];
                        \Illuminate\Support\Facades\Mail::to($targetEmail)->send(new \App\Mail\SpotlyNotificationMail($mailData));
                    }
                }

                $processedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "تمت معالجة {$processedCount} حجوزات منتهية وإرسال الإشعارات."
            ], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error in cleanup function: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

}

