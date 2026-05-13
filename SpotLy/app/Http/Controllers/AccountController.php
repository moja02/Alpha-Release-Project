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
}