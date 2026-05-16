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
use Illuminate\Support\Facades\Mail;

class AccountController extends Controller
{

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

            // توليد كلمة مرور عشوائية من 8 أحرف
            $generatedPassword = \Illuminate\Support\Str::random(8);

            \Illuminate\Support\Facades\DB::beginTransaction();

            // 1. إنشاء الحساب الأساسي
            $insertedAccountId = \Illuminate\Support\Facades\DB::table('accounts')->insertGetId([
                'name' => $inputName,
                'email' => $inputEmail,
                'phone' => $inputPhone,
                'password' => \Illuminate\Support\Facades\Hash::make($generatedPassword),
                'role' => $inputRole,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 2. إعداد جداول السائق (إذا كان الدور user)
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

            // 3. إدخال الإشعار في قاعدة البيانات (بدون كلمة المرور لأسباب أمنية)
            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'user_id' => $insertedAccountId,
                'message' => "مرحباً بك في SpotLy! تم إنشاء حسابك بنجاح. يرجى مراجعة بريدك الإلكتروني للحصول على بيانات الدخول.",
                'type' => 'Account_Created',
                'sent_to_email' => $inputEmail, // توثيق الإيميل الذي أرسلنا له
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 4. إرسال البريد الإلكتروني الفعلي بكلمة المرور للسائق
            if ($inputEmail) {
                $mailData = [
                    'title' => 'مرحباً بك في نظام SpotLy 🚗',
                    'body' => "أهلاً بك {$inputName}، لقد تم إنشاء حسابك بنجاح في نظام المواقف الذكية من قبل الإدارة.\n\n" .
                              "بيانات الدخول الخاصة بك هي:\n" .
                              "البريد الإلكتروني: {$inputEmail}\n" .
                              "كلمة المرور: {$generatedPassword}\n\n" .
                              "ملاحظة: نرجو منك الحفاظ على سرية بياناتك، ويمكنك تغيير كلمة المرور من إعدادات حسابك."
                ];
                \Illuminate\Support\Facades\Mail::to($inputEmail)->send(new \App\Mail\SpotlyNotificationMail($mailData));
            }

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

    public function getUserNotifications(Request $request)
    {
        try {
            $targetUserId = $request->input('userId');

            $userNotifications = \Illuminate\Support\Facades\DB::table('notifications')
                ->where('user_id', $targetUserId)
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $userNotifications
            ], 200);

        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
}