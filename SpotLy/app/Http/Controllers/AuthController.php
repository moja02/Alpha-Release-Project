<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * دالة تسجيل الدخول مفصولة في متحكم المصادقة (SRP)
     */
    public function login(Request $request)
    {
        // تطبيق قاعدة try/catch الإلزامية
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // استخدام متغيرات CamelCase
            $inputEmail = $request->input('email');
            $inputPassword = $request->input('password');

            // تعليق مضمن: البحث عن الحساب ومطابقة كلمة المرور
            $account = Account::where('email', $inputEmail)->first();

            if (!$account || !Hash::check($inputPassword, $account->password)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid email or password.'], 401);
            }

            $profileDetails = null;

            if ($account->role === 'user') {
                $profileDetails = User::where('account_id', $account->id)->first();
                
                // منع الدخول إذا كان الحساب محظوراً
                if ($profileDetails && $profileDetails->status === 'blocked') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Access Denied: Your account is blocked.'
                    ], 403);
                }
            } elseif ($account->role === 'employee') {
                $profileDetails = Employee::where('account_id', $account->id)->first();
            }

            $authToken = method_exists($account, 'createToken') 
                ? $account->createToken('ApiAuthToken')->plainTextToken 
                : 'stateless_session_active';

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
            Log::error('Error in AuthController login: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
    /**
     * دالة إرسال رمز التحقق OTP لاستعادة كلمة المرور
     */
    public function sendOtp(Request $request)
    {
        // تطبيق قاعدة try/catch الإلزامية
        try {
            // التحقق من أن البريد الإلكتروني مسجل فعلياً في جدول الحسابات
            $request->validate([
                'email' => 'required|string|email|exists:accounts,email',
            ]);

            // استخدام متغيرات camelCase
            $targetEmail = $request->input('email');
            $accountRecord = Account::where('email', $targetEmail)->first();

            // توليد رمز OTP عشوائي آمن مكون من 6 أرقام
            $generatedOtpCode = (string) random_int(100000, 999999);
            $expirationTime = now()->addMinutes(15);

            // مسح أي رموز سابقة لنفس البريد الإلكتروني لتجنب التضارب
            \App\Models\OtpCode::where('email', $targetEmail)->delete();

            // تخزين الرمز في قاعدة البيانات مشفراً لضمان أقصى درجات الأمان
            \App\Models\OtpCode::create([
                'email' => $targetEmail,
                'otp_code' => Hash::make($generatedOtpCode),
                'expires_at' => $expirationTime,
            ]);

            // صياغة محتوى الرسالة متضمنة الرمز الصريح ليتمكن المستخدم من قراءته
            $emailMessageContent = "طلب استعادة كلمة المرور. رمز الـ OTP الخاص بك هو: " . $generatedOtpCode . " (صالح لمدة 15 دقيقة).";

            // محاكاة إرسال البريد الإلكتروني عبر تخزينه في جدول الإشعارات
            \App\Models\Notification::create([
                'user_id' => $accountRecord->id,
                'message' => $emailMessageContent,
                'type' => 'Password_Reset_OTP',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني بنجاح.'
            ], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error('Error sending OTP: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إرسال الرمز: ' . $exception->getMessage()
            ], 500);
        }
    }

    /**
     * دالة التحقق من صحة الـ OTP وتحديث كلمة المرور الجديدة
     */
    public function verifyOtpAndResetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email|exists:accounts,email',
                'otpCode' => 'required|string',
                'newPassword' => 'required|string|min:6',
            ]);

            $inputEmail = $request->input('email');
            $inputOtpCode = $request->input('otpCode');
            $newPasswordInput = $request->input('newPassword');

            // جلب أحدث رمز OTP تم تدوينه للبريد المستهدف
            $otpRecord = \App\Models\OtpCode::where('email', $inputEmail)->latest()->first();

            // التحقق من وجود الرمز وصلاحيته الزمنية
            if (!$otpRecord || !now()->lessThanOrEqualTo($otpRecord->expires_at)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'رمز التحقق غير صحيح أو منتهي الصلاحية.'
                ], 400);
            }

            // مطابقة الرمز المدخل مع الرمز المشفر في قاعدة البيانات
            if (!Hash::check($inputOtpCode, $otpRecord->otp_code)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'رمز التحقق المدخل لا يطابق الرمز المرسل.'
                ], 400);
            }

            \Illuminate\Support\Facades\DB::beginTransaction();

            // تحديث كلمة المرور للحساب الأساسي وتشفيرها
            $targetAccount = Account::where('email', $inputEmail)->first();
            $targetAccount->password = Hash::make($newPasswordInput);
            $targetAccount->save();

            // مسح رمز التحقق نهائياً بعد استخدامه بنجاح لمنع إعادة الاستخدام
            \App\Models\OtpCode::where('email', $inputEmail)->delete();

            // إرسال إشعار تأكيدي بنجاح العملية
            \App\Models\Notification::create([
                'user_id' => $targetAccount->id,
                'message' => 'تم تغيير كلمة المرور الخاصة بحسابك بنجاح عبر التحقق الثنائي.',
                'type' => 'Password_Changed',
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إعادة تعيين كلمة المرور بنجاح.'
            ], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error resetting password: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إعادة تعيين كلمة المرور: ' . $exception->getMessage()
            ], 500);
        }
    }
}
