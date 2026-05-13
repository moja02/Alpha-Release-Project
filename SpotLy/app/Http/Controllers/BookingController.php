<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * دالة تسجيل حجز زائف وتطبيق الحظر التلقائي (FR1 / FR3)
     * تم نقلها إلى متحكم الحجوزات تطبيقاً لمبدأ المسؤولية المفردة (SRP)
     */
    public function recordFakeBooking(Request $request)
    {
        // تطبيق قاعدة try/catch الإلزامية
        try {
            $request->validate([
                'accountId' => 'required|integer|exists:users,account_id',
            ]);

            // استخدام متغيرات CamelCase
            $targetAccountId = $request->input('accountId');
            
            $driverUser = User::where('account_id', $targetAccountId)->first();

            // تعليق مضمن: زيادة عدد الحجوزات الزائفة بمقدار 1
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
}