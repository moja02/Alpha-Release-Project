<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use App\Models\Employee;
use App\Models\Notification; // لاستكمال سيناريو المخطط التسلسلي
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // لاستخدام المعاملات (Transactions) لضمان سلامة الإدخال
use Illuminate\Support\Str; //توليد الرمز العشوائي

class AccountController extends Controller
{
    /**
     * دالة تسجيل الدخول للنظام (تطبيق متطلب FR1)
     * تتحقق من الهوية وتوجه المستخدمين والموظفين حسب صلاحياتهم
     */
    public function login(Request $request)
    {
        
        try {
            // التحقق من صحة البيانات المرسلة من واجهة تسجيل الدخول
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            
            $inputEmail = $request->input('email');
            $inputPassword = $request->input('password');

            //  البحث عن الحساب الأساسي في قاعدة البيانات بواسطة البريد الإلكتروني
            $account = Account::where('email', $inputEmail)->first();

            // التحقق من وجود الحساب ومطابقة كلمة المرور المشفرة
            if (!$account || !Hash::check($inputPassword, $account->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid email or password.'
                ], 401);
            }

            //  جلب تفاصيل الملف الشخصي بناءً على الصلاحيات لتوجيهه للوحة التحكم المناسبة
            $profileDetails = null;

            if ($account->role === 'user') {
                $profileDetails = User::where('account_id', $account->id)->first();
                
                // فحص أمني: منع المستخدم من الدخول إذا كان حسابه معطلاً أو محظوراً
                if ($profileDetails && $profileDetails->status === 'blocked') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Access Denied: Your account has been blocked due to exceeding 3 fake bookings.'
                    ], 403);
                }
            } elseif ($account->role === 'employee') {
                $profileDetails = Employee::where('account_id', $account->id)->first();
            }

            //  توليد رمز مصادقة (Token) إذا تم تفعيل Laravel Sanctum للـ API
            $authToken = method_exists($account, 'createToken') 
                ? $account->createToken('ApiAuthToken')->plainTextToken 
                : 'stateless_session_active';

            // إرجاع استجابة JSON تحتوي على بيانات الهوية والصلاحية لتسهيل عرض لوحة التحكم
            return response()->json([
                'status' => 'success',
                'message' => 'Logged in successfully.',
                'token' => $authToken,
                'accountData' => [
                    'accountId' => $account->id,
                    'name' => $account->name,
                    'email' => $account->email,
                    'phone' => $account->phone,
                    'role' => $account->role,
                    'profile' => $profileDetails
                ]
            ], 200);

        } catch (\Exception $exception) {
            // تسجيل الخطأ الفني في السجل لضمان سهولة الصيانة
            Log::error('Error in login method: ' . $exception->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred during login: ' . $exception->getMessage()
            ], 500);
        }
    }

    public function createAccount(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|unique:accounts,email',
                'phone' => 'required|string|max:20',
                'role' => 'required|in:user,employee,admin',
                'plateNumber' => 'required_if:role,user|string'
            ]);

            $inputName = $request->input('name');
            $inputEmail = $request->input('email');
            $inputPhone = $request->input('phone');
            $inputRole = $request->input('role');
            $inputPlateNumber = $request->input('plateNumber');

            $generatedPassword = \Illuminate\Support\Str::random(8);

            \Illuminate\Support\Facades\DB::beginTransaction();

            $insertedAccountId = \Illuminate\Support\Facades\DB::table('accounts')->insertGetId([
                'name' => $inputName,
                'email' => $inputEmail,
                'phone' => $inputPhone,
                'password' => \Illuminate\Support\Facades\Hash::make($generatedPassword),
                'role' => $inputRole,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if ($inputRole === 'user') {
                \Illuminate\Support\Facades\DB::table('users')->insert([
                    'account_id' => $insertedAccountId,
                    'plate_number' => $inputPlateNumber,
                    'status' => 'active',
                    'fake_booking_count' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                \Illuminate\Support\Facades\DB::table('wallets')->insert([
                    'user_id' => $insertedAccountId,
                    'balance' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'user_id' => $insertedAccountId,
                'message' => "مرحباً بك في SpotLy! تم إنشاء حسابك. كلمة المرور الخاصة بك هي: {$generatedPassword}",
                'type' => 'Account_Created',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'accountId' => $insertedAccountId
            ], 201);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error($exception->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 500);
        }
    }
    /**
     * دالة تحديث البيانات الشخصية (FR1)
     * تسمح للموظف بتعديل رقم الهاتف، كلمة المرور، والحساب المصرفي
     */
    public function updateProfile(Request $request)
    {
        try {
            // التحقق من صحة المدخلات
            $request->validate([
                'accountId' => 'required|integer|exists:accounts,id',
                'phone' => 'required|string|max:20',
                'password' => 'nullable|string|min:6', // اختياري: فقط إذا أراد التغيير
                'bankAccountNumber' => 'nullable|string|max:50',
            ]);

            $targetId = $request->input('accountId');
            
            DB::beginTransaction();

            // 1. تحديث بيانات الحساب الأساسي (Account)
            $account = Account::findOrFail($targetId);
            $account->phone = $request->input('phone');
            
            // إذا قام المستخدم بكتابة كلمة مرور جديدة، يتم تشفيرها وحفظها
            if ($request->filled('password')) {
                $account->password = Hash::make($request->input('password'));
            }
            $account->save();

            // 2. تحديث بيانات الموظف (Employee) إن وجدت
            if ($account->role === 'employee') {
                $employee = Employee::where('account_id', $targetId)->first();
                if ($employee) {
                    $employee->bank_account_number = $request->input('bankAccountNumber');
                    $employee->save();
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully.',
                'updatedData' => [
                    'phone' => $account->phone,
                    'bankAccountNumber' => $account->role === 'employee' ? $employee->bank_account_number : null
                ]
            ], 200);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error updating profile: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    /**
     * جلب الإحصائيات الحية للسائق (عدد المخالفات وحالة الحساب)
     */
    public function getDriverStats(Request $request)
    {
        try {
            $userId = $request->input('userId');
            $driver = \Illuminate\Support\Facades\DB::table('users')->where('account_id', $userId)->first();
            
            if ($driver) {
                return response()->json([
                    'status' => 'success', 
                    'fake_booking_count' => $driver->fake_booking_count,
                    'account_status' => $driver->status
                ], 200);
            }
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
            
        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
}