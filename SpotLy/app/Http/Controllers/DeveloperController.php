<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeveloperController extends Controller
{
    // 1. عرض لوحة التحكم 
    public function index()
    {
        if (auth()->user()->role !== 'developer') {
            abort(403, 'غير مصرح لك!');
        }
        // جلب الإحصائيات من قاعدة البيانات لعرضها في قسم "نظرة عامة"
        $parkingsCount = DB::table('parkings')->count();
        
        // جلب عدد الموظفين (بافتراض أن جدول employees يحتوي على موظفي الميدان)
        $employeesCount = DB::table('employees')->count(); 

        // تمرير البيانات إلى الواجهة
        return view('developer.developer_dashboard', compact('parkingsCount', 'employeesCount'));
    }

    // 2. استقبال بيانات الخريطة وحفظ الموقف الجديد
    public function storeParking(Request $request)
    {
        try {
            // التحقق من صحة البيانات 
            $request->validate([
                'name' => 'required|string|max:191',
                'location_park' => 'required|string|max:191',
                'total_capacity' => 'required|integer|min:1',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            // إدراج الموقف في قاعدة البيانات
            \Illuminate\Support\Facades\DB::table('parkings')->insert([
                'name' => $request->name,
                'location_park' => $request->location_park, // إدراج الوصف/الموقع
                'total_capacity' => $request->total_capacity,
                'available_capacity' => $request->total_capacity, // الشواغر المبدئية
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'employee_id' => null, // ترك الموظف فارغاً لتعيينه لاحقاً 
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            return response()->json([
                'status' => 'success', 
                'message' => 'تم حفظ الموقف بنجاح وتعيين شواغره.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }
}