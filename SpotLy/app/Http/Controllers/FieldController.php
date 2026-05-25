<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Booking;

class FieldController extends Controller
{
    // 1. تسجيل دخول زائر (بدون حساب)
    public function guestEntry(Request $request)
    {
        try {
            $request->validate([
                'plate_number' => 'required|string|max:20'
            ]);

            $plateNumber = $request->input('plate_number');
            
            $employeeParkingId = auth()->user()->employee->parking_id; 

            DB::beginTransaction();

            // التحقق من سعة الموقف
            $parking = DB::table('parkings')->where('id', $employeeParkingId)->lockForUpdate()->first();
            if ($parking->available_capacity <= 0) {
                return response()->json(['status' => 'error', 'message' => 'الموقف ممتلئ بالكامل!'], 400);
            }

            // التحقق من عدم وجود حجز نشط لنفس اللوحة
            $exists = DB::table('bookings')
                ->where('guest_plate_number', $plateNumber)
                ->where('status', 'active')
                ->exists();

            if ($exists) {
                return response()->json(['status' => 'error', 'message' => 'هذه المركبة موجودة بالفعل داخل الموقف.'], 400);
            }

            // إنشاء تذكرة الزائر
            DB::table('bookings')->insert([
                'parking_id' => $employeeParkingId,
                'user_id' => null, // لأنه زائر
                'is_guest' => true,
                'guest_plate_number' => $plateNumber,
                'start_time' => Carbon::now(),
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            // إنقاص سعة الموقف المتاح
            DB::table('parkings')->where('id', $employeeParkingId)->decrement('available_capacity', 1);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم تسجيل دخول الزائر بنجاح وفتح تذكرة.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'حدث خطأ داخلي: ' . $e->getMessage()], 500);
        }
    }

    // 2. تسجيل خروج زائر وحساب التكلفة
    public function guestExit(Request $request)
    {
        try {
            $request->validate([
                'plate_number' => 'required|string|max:20'
            ]);

            $plateNumber = $request->input('plate_number');
            $employeeParkingId = auth()->user()->employee->parking_id; // نفس الموقف الخاص بالموظف

            DB::beginTransaction();

            // البحث عن التذكرة النشطة للزائر
            $booking = DB::table('bookings')
                ->where('guest_plate_number', $plateNumber)
                ->where('is_guest', true)
                ->where('status', 'active')
                ->where('parking_id', $employeeParkingId)
                ->first();

            if (!$booking) {
                return response()->json(['status' => 'error', 'message' => 'لم يتم العثور على سيارة زائر بهذه اللوحة.'], 404);
            }

            // حساب التكلفة والزمن
            $entryTime = Carbon::parse($booking->start_time);
            $exitTime = Carbon::now();
            $durationMinutes = $entryTime->diffInMinutes($exitTime);
            
            // في حالة كان التوقيت أقل من ساعة، نحتسبها ساعة كاملة كحد أدنى (التقريب للأعلى)
            $durationHours = ceil($durationMinutes / 60) == 0 ? 1 : ceil($durationMinutes / 60);
            
            // تسعيرة الزائر: مثلاً 3 دينار/نقاط للساعة الواحدة
            $hourlyRate = 2.5; 
            $totalCost = $durationHours * $hourlyRate;

            // تحديث الحجز كـ منتهي
            DB::table('bookings')->where('id', $booking->id)->update([
                'end_time' => $exitTime,
                'total_cost' => $totalCost,
                'status' => 'completed',
                'updated_at' => Carbon::now()
            ]);

            // تحرير مساحة في الموقف
            DB::table('parkings')->where('id', $employeeParkingId)->increment('available_capacity', 1);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'duration' => $durationHours,
                'cost' => $totalCost
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'حدث خطأ داخلي: ' . $e->getMessage()], 500);
        }
    }
}