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
     * دالة إنشاء حساب جديد (مستخدم أو موظف)
     */
    public function createAccount(Request $request)
    {
        
        try {
            // التحقق من المدخلات (كلمة المرور أصبحت مطلوبة للموظف فقط، ومستثناة للسائق)
            $request->validate([
                'name' => 'required|string|max:191',
                'email' => 'required|string|email|max:191|unique:accounts',
                'phone' => 'required|string|max:20',
                'role' => 'required|in:user,employee',
                'password' => 'required_if:role,employee|string|min:6',
                'plateNumber' => 'required_if:role,user|string|max:20', 
                'bankAccountNumber' => 'required_if:role,employee|string|max:50', 
            ]);

            DB::beginTransaction();

            
            $accountRole = $request->input('role');
            $plainPasswordCode = null;

            // تعليق مضمن: فحص نوع الحساب لتوليد الرمز العشوائي للسائقين فقط
            if ($accountRole === 'user') {
                // توليد رمز عشوائي آمن مكون من 8 خانات (حروف وأرقام)
                $plainPasswordCode = Str::random(8);
            } else {
                // الاعتماد على كلمة المرور المدخلة يدوياً للموظفين
                $plainPasswordCode = $request->input('password');
            }

            // حفظ بيانات الحساب الأساسي وتشفير الرمز العشوائي قبل تخزينه في الداتا بيز
            $newAccount = Account::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'password' => Hash::make($plainPasswordCode),
                'role' => $accountRole,
            ]);

            // تخصيص الكلاس الفرعي وتخزين البيانات المرتبطة
            if ($accountRole === 'user') {
                User::create([
                    'account_id' => $newAccount->id,
                    'plate_number' => $request->input('plateNumber'),
                    'status' => 'active',
                    'fake_booking_count' => 0,
                ]);

                // تعليق مضمن: صياغة رسالة البريد الإلكتروني متضمنة الرمز العشوائي المولد
                $welcomeEmailContent = "مرحباً بك في نظام SpotLy. تم إنشاء حسابك بنجاح. رمز الدخول العشوائي الخاص بك هو: " . $plainPasswordCode;

                // تخزين الرسالة في الداتا بيز (محاكاة إرسال البريد حسب متطلبات FR1)
                Notification::create([
                    'user_id' => $newAccount->id,
                    'message' => $welcomeEmailContent,
                    'type' => 'Welcome_Email',
                ]);

            } elseif ($accountRole === 'employee') {
                Employee::create([
                    'account_id' => $newAccount->id,
                    'bank_account_number' => $request->input('bankAccountNumber'),
                ]);
            }

            DB::commit();

            // إرجاع الرمز المولد في الاستجابة لتسهيل نسخه واختباره من قبل الموظف أو الدكتور
            return response::json([
                'status' => 'success',
                'message' => 'Account created successfully. Access code sent to user email.',
                'accountId' => $newAccount->id
            ], 201);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error in createAccount: ' . $exception->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create account: ' . $exception->getMessage()
            ], 500);
        }
    }
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
}