<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FieldController extends Controller
{
    // 1. تسجيل دخول زائر (بدون حساب)
    // 1. تسجيل دخول الزائر (معدلة للتأكيد)
    public function guestEntry(Request $request)
    {
        try {
            $request->validate([
                'plate_number' => 'required|string|max:191',
                'expected_exit_time' => 'required|date_format:H:i'
            ]);
            
            $plateNumber = $request->input('plate_number');
            $expectedTimeStr = $request->input('expected_exit_time');

            $expectedEndTime = Carbon::createFromFormat('H:i', $expectedTimeStr);
            if ($expectedEndTime->isPast()) {
                $expectedEndTime->addDay();
            }

            $userId = auth()->id() ?? $request->input('user_id'); 

            if (!$userId) {
                return response()->json(['status' => 'error', 'message' => 'لم يتم إرسال رقم الحساب من الواجهة.'], 403);
            }
            
            $employee = DB::table('employees')->where('account_id', $userId)->first();
            if (!$employee) {
                return response()->json(['status' => 'error', 'message' => 'هذا الحساب ليس موظفاً ميدانياً.'], 403);
            }

            $parking = DB::table('parkings')->where('employee_id', $employee->id)->first();
            if (!$parking) {
                return response()->json(['status' => 'error', 'message' => 'لا توجد ساحة وقوف معينة لهذا الموظف.'], 404);
            }
            $employeeParkingId = $parking->id;
            
            DB::beginTransaction();

            $parkingData = DB::table('parkings')->where('id', $employeeParkingId)->lockForUpdate()->first();
            if ($parkingData->available_capacity <= 0) {
                return response()->json(['status' => 'error', 'message' => 'الموقف ممتلئ بالكامل!'], 400);
            }

            $exists = DB::table('bookings')
                ->where('plate_number', $plateNumber)
                ->where('is_guest', 1)
                ->where('status', 'active')
                ->exists();

            if ($exists) {
                return response()->json(['status' => 'error', 'message' => 'هذه المركبة موجودة بالفعل داخل الموقف.'], 400);
            }

            DB::table('bookings')->insert([
                'parking_id' => $employeeParkingId,
                'user_id' => null, 
                'is_guest' => 1,
                'plate_number' => $plateNumber,
                'start_time' => Carbon::now(),
                'end_time' => $expectedEndTime, 
                'type' => 'actual', 
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            DB::table('parkings')->where('id', $employeeParkingId)->decrement('available_capacity', 1);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'تم تسجيل دخول الزائر وفتح تذكرة بنجاح.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'خطأ داخلي: ' . $e->getMessage()], 500);
        }
    }

    // 2. تسجيل خروج زائر وحساب التكلفة مع تطبيق العقوبة عند التأخير
    public function guestExit(Request $request)
    {
        try {
            $request->validate(['plate_number' => 'required|string|max:191']);
            $plateNumber = $request->input('plate_number');

            $userId = auth()->id() ?? $request->input('user_id'); 
            if (!$userId) return response()->json(['status' => 'error', 'message' => 'انتهت الجلسة.'], 403);

            $employee = DB::table('employees')->where('account_id', $userId)->first();
            if (!$employee) return response()->json(['status' => 'error', 'message' => 'غير مصرح'], 403);
            
            $parking = DB::table('parkings')->where('employee_id', $employee->id)->first();
            if (!$parking) return response()->json(['status' => 'error', 'message' => 'لا يوجد موقف'], 404);
            
            $employeeParkingId = $parking->id;
            
            DB::beginTransaction();

            $booking = DB::table('bookings')
                ->where('plate_number', $plateNumber)
                ->where('is_guest', 1)
                ->where('status', 'active')
                ->where('parking_id', $employeeParkingId)
                ->first();

            if (!$booking) {
                return response()->json(['status' => 'error', 'message' => 'لم يتم العثور على سيارة زائر بهذه اللوحة.'], 404);
            }

            $entryTime = Carbon::parse($booking->start_time);
            $expectedExitTime = Carbon::parse($booking->end_time); // جلب وقت الخروج المتوقع الذي خزنّاه عند الدخول
            $exitTime = Carbon::now(); // وقت الخروج الفعلي الحالي
            
            // 1. حساب التكلفة الأساسية (بناءً على ساعات التواجد الفعلية والتسعيرة 2.5)
            $durationMinutes = $entryTime->diffInMinutes($exitTime);
            $durationHours = ceil($durationMinutes / 60) == 0 ? 1 : ceil($durationMinutes / 60);
            
            $hourlyRate = 2.5; 
            $baseCost = $durationHours * $hourlyRate;
            $totalCost = $baseCost;

            // 2. تطبيق قاعدة العقوبة الذكية عند التأخير
            $penaltyPoints = 0;
            $penaltyMessage = "";
            
            if ($exitTime->gt($expectedExitTime)) {
                $delayMinutes = $exitTime->diffInMinutes($expectedExitTime);
                $penaltyPoints = ceil($delayMinutes / 30); // نقطة واحدة لكل 30 دقيقة تأخير أو كسرها
                $totalCost += $penaltyPoints; // إضافة قيمة العقوبة للتكلفة الإجمالية للزائر للكاش
                $penaltyMessage = " (تشمل غرامة تأخير: " . $penaltyPoints . " نقطة لتأخير قدره " . $delayMinutes . " دقيقة)";
            }

            // 3. تحديث بيانات الحجز وحفظ وقت الخروج الفعلي في حقل (actual_exit_time) إن وجد،
            // أو تحديث حقل end_time بعد أن أنهينا المقارنة والحسابات بنجاح.
            DB::table('bookings')->where('id', $booking->id)->update([
                'end_time' => $exitTime, // تحديثه بوقت الخروج الفعلي لإغلاق الحجز
                'status' => 'completed',
                'updated_at' => Carbon::now()
            ]);

            DB::table('parkings')->where('id', $employeeParkingId)->increment('available_capacity', 1);

            DB::commit();
            
            // سنرسل رسالة النجاح والـ message المنسقة ليتم عرضها في السويت أليرت (Swal) كـ كاش للدفع المباشر
            $formattedMessage = "المدة الفعلية: " . $durationHours . " ساعة. القيمة المطلوبة كاش: " . $totalCost . " دل." . $penaltyMessage;
            
            return response()->json([
                'status' => 'success', 
                'duration' => $durationHours, 
                'cost' => $totalCost,
                'message' => $formattedMessage
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'خطأ داخلي: ' . $e->getMessage()], 500);
        }
    }
}