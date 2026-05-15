<?php

namespace App\Http\Controllers;

use App\Models\Parking;
use App\Models\Booking;
use App\Models\Wallet;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * دالة تسجيل حجز زائف وتطبيق الحظر التلقائي (FR1 / FR3)
     * تم نقلها إلى متحكم الحجوزات تطبيقاً لمبدأ المسؤولية المفردة (SRP)
     */
    public function recordFakeBooking(Request $request)
    {
        
        try {
            $request->validate([
                'accountId' => 'required|integer|exists:users,account_id',
            ]);

            
            $targetAccountId = $request->input('accountId');
            
            $driverUser = User::where('account_id', $targetAccountId)->first();

            //  زيادة عدد الحجوزات الزائفة بمقدار 1
            $currentFakeBookingCount = $driverUser->fake_booking_count + 1;
            $driverUser->fake_booking_count = $currentFakeBookingCount;

            $responseMessage = 'Fake booking recorded successfully.';

            // التحقق مما إذا كان العداد قد وصل إلى 3 لتطبيق الحظر التلقائي
            if ($currentFakeBookingCount >= 3) {
                $driverUser->status = 'blocked';
                $responseMessage = 'Account automatically blocked due to exceeding 3 fake bookings.';

                Notification::create([
                    'user_id' => $targetAccountId,
                    'message' => 'Your account has been blocked because you exceeded the limit of 3 unfulfilled initial bookings.',
                    'type' => 'Account_Blocked',
                ]);
            }

            $driverUser->save();

            return response()->json([
                'status' => 'success',
                'message' => $responseMessage,
                'currentFakeBookingCount' => $currentFakeBookingCount,
                'accountStatus' => $driverUser->status
            ], 200);

        } catch (\Exception $exception) {
            Log::error('Error in BookingController recordFakeBooking: ' . $exception->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record fake booking: ' . $exception->getMessage()
            ], 500);
        }
    }

     
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
            $request->validate(['userId' => 'required|integer']);
            $targetUserId = $request->input('userId');

            //  البحث عن حجز مؤكد مرتبط بحساب السائق وربطه بساحة الوقوف
            $activeBookingRecord = DB::table('bookings')
                ->join('parkings', 'bookings.parking_id', '=', 'parkings.id')
                ->where('bookings.user_id', $targetUserId)
                ->where('bookings.status', 'confirmed') // مطابقة حالة قاعدة البيانات
                ->select('bookings.*', 'parkings.name as parking_name', 'parkings.location_park')
                ->first();

            return response()->json([
                'status' => 'success',
                'hasActiveBooking' => !is_null($activeBookingRecord),
                'bookingData' => $activeBookingRecord
            ], 200);
        } catch (\Exception $exception) {
            Log::error('Error fetching active booking: ' . $exception->getMessage());
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
                // الحجز المبدئي: يبدأ الآن وينتهي بعد 20 دقيقة (المهلة)
                $startTime = now();
                $endTime = now()->addMinutes(20);
                $notificationMsg = "تم إنشاء حجز مبدئي. أمامك 20 دقيقة للوصول للموقف.";
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

}