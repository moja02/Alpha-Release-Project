<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ManagerController extends Controller
{
    /**
     * عرض لوحة تحكم المدير
     */
    public function index()
    {
        try {
            $user = auth()->user();

            if (!$user || $user->role !== 'manager') {
                abort(403, 'غير مصرح لك بالدخول!');
            }

            // جلب سجل المدير من جدول managers
            $manager = DB::table('managers')->where('account_id', $user->id)->first();

            if (!$manager) {
                abort(403, 'لا يوجد ملف تعريف مدير مرتبط بهذا الحساب!');
            }

            // في حال تم حظر المدير، يتم تسجيل خروجه وتوجيهه لصفحة الدخول
            if ($manager->status === 'blocked') {
                Auth::guard('web')->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();
                return redirect('/login')->with('error', 'تم حظر حسابك من قبل الإدارة!');
            }

            // قائمة الساحات المدارة مع تفاصيل الموظف المسؤول
            $parkingsList = DB::table('parkings')
                ->leftJoin('employees', 'parkings.employee_id', '=', 'employees.id')
                ->leftJoin('accounts', 'employees.account_id', '=', 'accounts.id')
                ->select(
                    'parkings.id',
                    'parkings.name as parking_name',
                    'parkings.location_park',
                    'parkings.total_capacity',
                    'parkings.available_capacity',
                    'parkings.employee_id',
                    'accounts.name as employee_name',
                    'accounts.phone as employee_phone'
                )
                ->where('parkings.manager_id', $manager->id)
                ->get();

            // قائمة الموظفين غير المعينين لأي ساحة وقوف حالياً
            $unassignedEmployees = DB::table('employees')
                ->join('accounts', 'employees.account_id', '=', 'accounts.id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                          ->from('parkings')
                          ->whereColumn('parkings.employee_id', 'employees.id');
                })
                ->select(
                    'employees.id',
                    'accounts.name as employee_name',
                    'accounts.phone as employee_phone'
                )
                ->get();

            return view('dashboards.manager', compact('parkingsList', 'unassignedEmployees'));

        } catch (\Exception $exception) {
            abort(500, 'حدث خطأ داخلي أثناء تحميل لوحة تحكم المدير: ' . $exception->getMessage());
        }
    }

    public function storeEmployee(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $assignmentType = $request->input('assignment_type', 'create');

            if ($assignmentType === 'select') {
                // التحقق من صحة اختيار موظف موجود
                $request->validate([
                    'employee_id' => 'required|integer|exists:employees,id',
                    'parking_id' => 'required|integer|exists:parkings,id',
                ]);

                // التأكد من أن الموظف غير معين بالفعل لموقف آخر
                $alreadyAssigned = DB::table('parkings')
                    ->where('employee_id', $request->employee_id)
                    ->exists();

                if ($alreadyAssigned) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'هذا الموظف معين بالفعل لساحة أخرى!'
                    ], 422);
                }

                // ربط الموظف بالساحة
                DB::table('parkings')
                    ->where('id', $request->parking_id)
                    ->update([
                        'employee_id' => $request->employee_id,
                        'updated_at' => now()
                    ]);

                $empDetails = DB::table('employees')
                    ->join('accounts', 'employees.account_id', '=', 'accounts.id')
                    ->where('employees.id', $request->employee_id)
                    ->select('employees.id', 'accounts.name', 'accounts.phone')
                    ->first();

                return response()->json([
                    'status' => 'success',
                    'message' => 'تم تعيين الموظف للساحة بنجاح.',
                    'employee' => [
                        'id' => $empDetails->id,
                        'name' => $empDetails->name,
                        'phone' => $empDetails->phone
                    ]
                ]);
            } else {
                // التحقق من صحة البيانات لإنشاء حساب جديد
                $request->validate([
                    'name' => 'required|string|max:191',
                    'email' => 'required|string|email|max:191|unique:accounts,email',
                    'phone' => 'required|string|max:20',
                    'bank_account_number' => 'nullable|string|max:50',
                    'parking_id' => 'required|integer|exists:parkings,id',
                ]);

                $generatedPassword = Str::random(8);

                DB::beginTransaction();

                // 1. إدراج الحساب في جدول accounts
                $accountId = DB::table('accounts')->insertGetId([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($generatedPassword),
                    'role' => 'employee',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2. إدراج في جدول employees
                $employeeId = DB::table('employees')->insertGetId([
                    'account_id' => $accountId,
                    'bank_account_number' => $request->bank_account_number ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 3. ربط الموظف بالساحة المحددة
                DB::table('parkings')
                    ->where('id', $request->parking_id)
                    ->update([
                        'employee_id' => $employeeId,
                        'updated_at' => now()
                    ]);

                // 4. إرسال البريد الإلكتروني للموظف
                try {
                    $mailData = [
                        'title' => 'مرحباً بك في نظام SpotLy 🚗',
                        'body' => "أهلاً بك {$request->name}، لقد تم إنشاء حساب الموظف الميداني الخاص بك بنجاح وتعيينك للساحة.\n\n" .
                                  "بيانات الدخول الخاصة بك هي:\n" .
                                  "البريد الإلكتروني: {$request->email}\n" .
                                  "كلمة المرور: {$generatedPassword}\n\n" .
                                  "ملاحظة: نرجو منك الحفاظ على سرية بياناتك، ويمكنك تغيير كلمة المرور من إعدادات حسابك."
                    ];
                    \Illuminate\Support\Facades\Mail::to($request->email)->send(new \App\Mail\SpotlyNotificationMail($mailData));
                } catch (\Exception $mailEx) {
                    \Illuminate\Support\Facades\Log::warning('فشل إرسال بريد الموظف الجديد: ' . $mailEx->getMessage());
                }

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'تم إنشاء حساب الموظف وتعيينه للساحة بنجاح.',
                    'employee' => [
                        'id' => $employeeId,
                        'name' => $request->name,
                        'phone' => $request->phone
                    ]
                ]);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
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

    /**
     * فك ارتباط الموظف عن الساحة
     */
    public function unlinkEmployee(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $request->validate([
                'parking_id' => 'required|integer|exists:parkings,id',
            ]);

            DB::table('parkings')
                ->where('id', $request->parking_id)
                ->update([
                    'employee_id' => null,
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم فك ارتباط الموظف عن الساحة بنجاح.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }
}
