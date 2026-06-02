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

        // جلب قائمة المدراء
        $managers = DB::table('accounts')->where('role', 'manager')->get();

        // تمرير البيانات إلى الواجهة
        return view('developer.developer_dashboard', compact('parkingsCount', 'employeesCount', 'managers'));
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

    // 3. إنشاء حساب مدير جديد
    public function storeManager(Request $request)
    {
        if (auth()->user()->role !== 'developer') {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك!'
            ], 403);
        }

        try {
            // التحقق من صحة البيانات 
            $request->validate([
                'name' => 'required|string|max:191',
                'email' => 'required|string|email|max:191|unique:accounts,email',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:6',
            ]);

            // إدراج الحساب في قاعدة البيانات
            $insertedId = DB::table('accounts')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => 'manager',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            return response()->json([
                'status' => 'success', 
                'message' => 'تم إنشاء حساب المدير بنجاح.',
                'manager' => [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'created_at' => \Carbon\Carbon::now()->format('Y-m-d H:i')
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error', 
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }
}