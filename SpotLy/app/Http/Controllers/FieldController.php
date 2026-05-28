<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FieldController extends Controller
{
    // 1. تسجيل دخول زائر (بدون حساب)
    public function guestEntry(Request $request)
    {
        try {
            $request->validate([
                'plate_number' => 'required|string|max:20',
                'expected_exit_time' => 'required|date_format:H:i' // التحقق من صيغة الوقت الساعات:الدقائق
            ]);
            
            $plateNumber = $request->input('plate_number');
            $expectedTimeStr = $request->input('expected_exit_time');

            // دمج الوقت المدخل مع تاريخ اليوم
            $expectedEndTime = Carbon::createFromFormat('H:i', $expectedTimeStr);
            
            // إذا كان الوقت المدخل أقدم من الوقت الحالي (الزائر دخل ليلاً وسيخرج فجراً في اليوم التالي)
            if ($expectedEndTime->isPast()) {
                $expectedEndTime->addDay();
            }

            
            $employee = DB::table('employees')->where('account_id', auth()->id())->first();
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
                ->where('guest_plate_number', $plateNumber)
                ->where('status', 'active')
                ->exists();

            if ($exists) {
                return response()->json(['status' => 'error', 'message' => 'هذه المركبة موجودة بالفعل داخل الموقف.'], 400);
            }

            DB::table('bookings')->insert([
                'parking_id' => $employeeParkingId,
                'user_id' => null, // زائر
                'is_guest' => true,
                'guest_plate_number' => $plateNumber,
                'start_time' => Carbon::now(),
                'end_time' => $expectedEndTime, // حفظ وقت الخروج المتوقع
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

    // 2. تسجيل خروج زائر وحساب التكلفة
    public function guestExit(Request $request)
    {
        try {
            $request->validate(['plate_number' => 'required|string|max:20']);
            $plateNumber = $request->input('plate_number');

            $employee = DB::table('employees')->where('account_id', auth()->id())->first();
            if (!$employee) return response()->json(['status' => 'error', 'message' => 'غير مصرح'], 403);
            $parking = DB::table('parkings')->where('employee_id', $employee->id)->first();
            if (!$parking) return response()->json(['status' => 'error', 'message' => 'لا يوجد موقف'], 404);
            $employeeParkingId = $parking->id;
            

            DB::beginTransaction();

            $booking = DB::table('bookings')
                ->where('guest_plate_number', $plateNumber)
                ->where('is_guest', true)
                ->where('status', 'active')
                ->where('parking_id', $employeeParkingId)
                ->first();

            if (!$booking) {
                return response()->json(['status' => 'error', 'message' => 'لم يتم العثور على سيارة زائر بهذه اللوحة.'], 404);
            }

            $entryTime = Carbon::parse($booking->start_time);
            $exitTime = Carbon::now();
            $durationMinutes = $entryTime->diffInMinutes($exitTime);
            $durationHours = ceil($durationMinutes / 60) == 0 ? 1 : ceil($durationMinutes / 60);
            
            $hourlyRate = 2.5; // التسعيرة
            $totalCost = $durationHours * $hourlyRate;

            DB::table('bookings')->where('id', $booking->id)->update([
                'end_time' => $exitTime,
                'total_cost' => $totalCost,
                'status' => 'completed',
                'updated_at' => Carbon::now()
            ]);

            DB::table('parkings')->where('id', $employeeParkingId)->increment('available_capacity', 1);

            DB::commit();
            return response()->json(['status' => 'success', 'duration' => $durationHours, 'cost' => $totalCost]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'خطأ داخلي: ' . $e->getMessage()], 500);
        }
    }
}