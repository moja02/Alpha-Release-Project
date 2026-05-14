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
            $request->validate([
                'userId' => 'required|integer',
                'parkingId' => 'required|integer',
                'paymentMethod' => 'required|in:wallet,cash'
            ]);

            $inputUserId = $request->input('userId');
            $inputParkingId = $request->input('parkingId');
            $selectedPaymentMethod = $request->input('paymentMethod');

            DB::beginTransaction();

            //  فحص وجود حجز مسبق لتجنب الحجوزات المزدوجة
            $hasExistingBooking = DB::table('bookings')
                ->where('user_id', $inputUserId)
                ->where('status', 'confirmed')
                ->sharedLock()
                ->exists();

            if ($hasExistingBooking) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'لديك حجز نشط بالفعل.'
                ], 400);
            }

            // فحص السعة المتاحة في الساحة المستهدفة (تطابقاً مع حقل available_capacity)
            $targetParkingArea = DB::table('parkings')->where('id', $inputParkingId)->lockForUpdate()->first();

            if (!$targetParkingArea || $targetParkingArea->available_capacity <= 0) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'عذراً، هذه الساحة ممتلئة بالكامل حالياً.'
                ], 400);
            }

            // جلب رقم اللوحة من جدول المستخدمين المرتبط بالحساب
            $driverProfile = DB::table('users')->where('account_id', $inputUserId)->first();
            $driverPlateNumber = $driverProfile ? $driverProfile->plate_number : 'غير محدد';

            //  معالجة الدفع عبر المحفظة (خصم 10 نقاط كأجرة حجز)
            $bookingCostAmount = 10;
            if ($selectedPaymentMethod === 'wallet') {
                $userWallet = DB::table('wallets')->where('user_id', $inputUserId)->lockForUpdate()->first();
                
                if (!$userWallet || $userWallet->balance < $bookingCostAmount) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'رصيد محفظتك الرقمية غير كافٍ لإتمام الحجز.'
                    ], 400);
                }
                
                DB::table('wallets')->where('user_id', $inputUserId)->decrement('balance', $bookingCostAmount);
            }

            // إنقاص السعة المتاحة بمقدار 1 للساحة المحددة
            DB::table('parkings')->where('id', $inputParkingId)->decrement('available_capacity', 1);

            //  إدراج الحجز مع تحديد المهلة الزمنية (15 دقيقة للحجز المبدئي)
            $startTime = now();
            $endTime = now()->addMinutes(15);

            $insertedBookingId = DB::table('bookings')->insertGetId([
                'user_id' => $inputUserId,
                'parking_id' => $inputParkingId,
                'plate_number' => $driverPlateNumber,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'type' => 'initial',
                'status' => 'confirmed',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // إرسال إشعار ترحيبي
            DB::table('notifications')->insert([
                'user_id' => $inputUserId,
                'message' => "تم تأكيد حجزك في ({$targetParkingArea->name}). المهلة المتاحة للوصول تنتهي في " . $endTime->format('H:i') . ".",
                'type' => 'Booking_Confirmed',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم تأكيد الحجز بنجاح.',
                'bookingId' => $insertedBookingId
            ], 201);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error processing booking: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

}