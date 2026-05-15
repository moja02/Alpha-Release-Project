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
            // 1. التحقق من صحة البيانات القادمة من واجهة المستخدم
            $request->validate([
                'userId' => 'required|integer|exists:users,account_id',
                'parkingId' => 'required|integer|exists:parkings,id',
                'paymentMethod' => 'required|in:wallet,cash' // رغم عدم وجوده في الجدول، نحتاجه لمعالجة الخصم
            ]);

            $inputUserId = $request->input('userId');
            $inputParkingId = $request->input('parkingId');
            $selectedPaymentMethod = $request->input('paymentMethod');

            \Illuminate\Support\Facades\DB::beginTransaction();

            // 2. التحقق من عدم وجود حجز مبدئي أو فعلي مسبق للسائق
            $hasExistingBooking = \Illuminate\Support\Facades\DB::table('bookings')
                ->where('user_id', $inputUserId)
                ->where('status', 'confirmed')
                ->sharedLock()
                ->exists();

            if ($hasExistingBooking) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'عذراً، لديك حجز نشط بالفعل. لا يمكنك حجز موقف جديد حتى يكتمل أو يُلغى الحجز الحالي.'
                ], 400);
            }

            // 3. التحقق الحرفي من وجود سعة متاحة في الساحة المختارة
            $targetParkingArea = \Illuminate\Support\Facades\DB::table('parkings')
                ->where('id', $inputParkingId)
                ->lockForUpdate() // تأمين السجل لمنع تعارض الحجوزات في نفس اللحظة
                ->first();

            if (!$targetParkingArea || $targetParkingArea->available_capacity <= 0) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'عذراً، هذه الساحة ممتلئة بالكامل حالياً، يرجى اختيار ساحة أخرى.'
                ], 400);
            }

            // 4. جلب رقم اللوحة الخاص بالسائق (مطلوب كحقل إلزامي في جدول bookings)
            $driverProfile = \Illuminate\Support\Facades\DB::table('users')->where('account_id', $inputUserId)->first();
            $driverPlateNumber = $driverProfile ? $driverProfile->plate_number : 'غير محدد';

            // 5. معالجة الدفع المسبق (إذا اختار المحفظة)
            $bookingCostAmount = 10;
            if ($selectedPaymentMethod === 'wallet') {
                $userWallet = \Illuminate\Support\Facades\DB::table('wallets')->where('user_id', $inputUserId)->lockForUpdate()->first();
                
                if (!$userWallet || $userWallet->balance < $bookingCostAmount) {
                    return response()->json([
                        'status' => 'error', 
                        'message' =>'رصيد المحفظة غير كافٍ. يرجى الشحن.'
                    ], 400);
                }
                
                // خصم الرصيد
                \Illuminate\Support\Facades\DB::table('wallets')->where('user_id', $inputUserId)->decrement('balance', $bookingCostAmount);
            }

            // 6. إنقاص السعة المتاحة في الساحة بمقدار 1
            \Illuminate\Support\Facades\DB::table('parkings')->where('id', $inputParkingId)->decrement('available_capacity', 1);

            // 7. إنشاء الحجز العادي (المبدئي) مع تحديد مهلة 15 دقيقة
            $startTime = now();
            $endTime = now()->addMinutes(15);

            $insertedBookingId = \Illuminate\Support\Facades\DB::table('bookings')->insertGetId([
                'user_id' => $inputUserId,
                'parking_id' => $inputParkingId,
                'plate_number' => $driverPlateNumber,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'type' => 'initial', // نوع الحجز مبدئي حسب الجداول
                'status' => 'confirmed', // حالة الحجز مؤكدة مبدئياً
                'created_at' => now(),
                'updated_at' => now()
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إنشاء الحجز بنجاح.',
                'bookingId' => $insertedBookingId
            ], 201);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error creating regular booking: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

}