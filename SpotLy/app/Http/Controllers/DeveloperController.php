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
        try {
            if (auth()->user()->role !== 'developer') {
                abort(403, 'غير مصرح لك!');
            }
            // جلب الإحصائيات من قاعدة البيانات لعرضها في قسم "نظرة عامة"
            $parkingsCount = DB::table('parkings')->count();
            
            // جلب عدد الموظفين 
            $employeesCount = DB::table('employees')->count(); 

            // جلب قائمة الساحات بالكامل لإظهارها على الخريطة
            $parkingsList = DB::table('parkings')->get(['id', 'name', 'latitude', 'longitude', 'total_capacity', 'available_capacity']);

            // جلب قائمة موظفي الميدان المسجلين في النظام مع المواقف المرتبطة بهم إن وجدت
            $employeesList = DB::table('accounts')
                ->join('employees', 'accounts.id', '=', 'employees.account_id')
                ->leftJoin('parkings', 'employees.id', '=', 'parkings.employee_id')
                ->select(
                    'accounts.id as account_id', 
                    'employees.id as employee_id', 
                    'accounts.name', 
                    'accounts.email', 
                    'accounts.phone', 
                    'employees.bank_account_number', 
                    'accounts.created_at',
                    'parkings.name as parking_name'
                )
                ->get();

            // جلب قائمة المدراء مع حالتهم 
            $managers = DB::table('accounts')
                ->leftJoin('managers', 'accounts.id', '=', 'managers.account_id')
                ->select(
                    'accounts.id', 
                    'accounts.name', 
                    'accounts.email', 
                    'accounts.phone', 
                    'accounts.created_at', 
                    DB::raw('COALESCE(managers.status, "active") as status')
                )
                ->where('accounts.role', 'manager')
                ->get();

            // تمرير البيانات إلى الواجهة
            return view('developer.developer_dashboard', compact('parkingsCount', 'employeesCount', 'managers', 'parkingsList', 'employeesList'));
        } catch (\Exception $exception) {
            abort(500, 'حدث خطأ داخلي: ' . $exception->getMessage());
        }
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
            $insertedId = \Illuminate\Support\Facades\DB::table('parkings')->insertGetId([
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
                'message' => 'تم حفظ الموقف بنجاح وتعيين شواغره.',
                'parking' => [
                    'id' => $insertedId,
                    'name' => $request->name,
                    'total_capacity' => $request->total_capacity,
                    'available_capacity' => $request->total_capacity,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ]
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

            DB::beginTransaction();

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

            // إدراج سجل في جدول managers
            DB::table('managers')->insert([
                'account_id' => $insertedId,
                'status' => 'active',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success', 
                'message' => 'تم إنشاء حساب المدير بنجاح.',
                'manager' => [
                    'id' => $insertedId,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'status' => 'active',
                    'created_at' => \Carbon\Carbon::now()->format('Y-m-d H:i')
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }

    // 4. تغيير حالة تفعيل المدير (تنشيط / تعطيل)
    public function toggleManagerStatus($id)
    {
        if (auth()->user()->role !== 'developer') {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك!'
            ], 403);
        }

        try {
            // التحقق من وجود الحساب
            $account = DB::table('accounts')->where('id', $id)->where('role', 'manager')->first();
            if (!$account) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'حساب المدير غير موجود.'
                ], 404);
            }

            // التحقق من وجود سجل في جدول managers
            $managerProfile = DB::table('managers')->where('account_id', $id)->first();
            if (!$managerProfile) {
                DB::table('managers')->insert([
                    'account_id' => $id,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $currentStatus = 'active';
            } else {
                $currentStatus = $managerProfile->status;
            }

            $newStatus = ($currentStatus === 'active') ? 'blocked' : 'active';

            DB::table('managers')
                ->where('account_id', $id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => 'success',
                'message' => ($newStatus === 'active') ? 'تم تفعيل حساب المدير بنجاح.' : 'تم تعطيل حساب المدير بنجاح.',
                'new_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }
}