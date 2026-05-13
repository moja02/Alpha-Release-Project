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

class AccountController extends Controller
{
    /**
     * دالة إنشاء حساب جديد (مستخدم أو موظف)
     */
    public function createAccount(Request $request)
    {
        
        try {
            // التحقق من صحة البيانات المدخلة
            $request->validate([
                'name' => 'required|string|max:191',
                'email' => 'required|string|email|max:191|unique:accounts',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:6',
                'role' => 'required|in:user,employee',
                // حقل اللوحة مطلوب فقط إذا كان الحساب للمستخدم (السائق)
                'plateNumber' => 'required_if:role,user|string|max:20', 
                // حقل الحساب البنكي مطلوب إذا كان الحساب للموظف
                'bankAccountNumber' => 'required_if:role,employee|string|max:50', 
            ]);

            // استخدام DB Transaction لضمان أنه إذا فشل إنشاء الكلاس الابن، يتم التراجع عن الأب
            DB::beginTransaction();

            //  تهيئة البيانات الأساسية الموروثة أولاً عبر كلاس Account
            $account = Account::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->input('password')),
                'role' => $request->input('role'),
            ]);

            //  تخصيص الكائن الفرعي بناءً على نوع الحساب (Role)
            if ($request->input('role') === 'user') {
                // حفظ بيانات السائق وربطها بالحساب الأساسي
                User::create([
                    'account_id' => $account->id,
                    'plate_number' => $request->input('plateNumber'), // استخدام التسمية القياسية
                    'status' => 'active', // تعيين الحالة الافتراضية
                    'fake_booking_count' => 0,
                ]);

                // إرسال إشعار ترحيبي 
                Notification::create([
                    'user_id' => $account->id, // نربطه بـ id الحساب الأساسي
                    'message' => 'أهلاً بك في SpotLy. تم إنشاء حسابك بنجاح.',
                    'type' => 'Welcome_Email',
                ]);

            } elseif ($request->input('role') === 'employee') {
                // حفظ بيانات الموظف
                Employee::create([
                    'account_id' => $account->id,
                    'bank_account_number' => $request->input('bankAccountNumber'),
                ]);
            }

            // تأكيد حفظ كافة البيانات في الجداول
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully.',
                'accountId' => $account->id
            ], 201);

        } catch (\Exception $exception) {
            // التراجع عن الإدخال في حال حدوث أي خطأ برمجياً
            DB::rollBack();
            
            // تسجيل الخطأ في ملف الـ Log
            Log::error('Error in createAccount: ' . $exception->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create account: ' . $exception->getMessage()
            ], 500);
        }
    }
    /**
     * دالة تسجيل حجز زائف وتطبيق الحظر التلقائي
     * يتم استدعاؤها برمجياً عند انتهاء مهلة الحجز المبدئي دون حضور السائق
     */
    public function recordFakeBooking(Request $request)
    {
        try {
            // التحقق من صحة المدخلات والتأكد من وجود الحساب
            $request->validate([
                'accountId' => 'required|integer|exists:users,account_id',
            ]);

            $targetAccountId = $request->input('accountId');
            
            // جلب كائن السائق المرتبط بالحساب الأساسي
            $driverUser = User::where('account_id', $targetAccountId)->first();

            // زيادة عدد الحجوزات الزائفة لتتبع الإلغاءات التلقائية بمقدار 1
            $currentFakeBookingCount = $driverUser->fake_booking_count + 1;
            $driverUser->fake_booking_count = $currentFakeBookingCount;

            $responseMessage = 'Fake booking recorded successfully.';

            //  التحقق مما إذا كان العداد قد وصل إلى 3 لتطبيق الحظر التلقائي
            if ($currentFakeBookingCount >= 3) {
                // تحديث حالة الحساب ليصبح محظوراً لمنع التلاعب
                $driverUser->status = 'blocked';
                $responseMessage = 'Account has been automatically blocked due to exceeding 3 fake bookings.';

                // تسجيل إشعار فوري للمستخدم بقرار الحظر
                Notification::create([
                    'user_id' => $targetAccountId,
                    'message' => 'Your account has been blocked because you exceeded the limit of 3 unfulfilled initial bookings.',
                    'type' => 'Account_Blocked',
                ]);
            }

            // حفظ التحديثات في قاعدة البيانات
            $driverUser->save();

            return response()->json([
                'status' => 'success',
                'message' => $responseMessage,
                'currentFakeBookingCount' => $currentFakeBookingCount,
                'accountStatus' => $driverUser->status
            ], 200);

        } catch (\Exception $exception) {
            // تسجيل الخطأ الداخلي لضمان سهولة الصيانة وتتبع الأخطاء
            Log::error('Error in recordFakeBooking: ' . $exception->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to record fake booking: ' . $exception->getMessage()
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