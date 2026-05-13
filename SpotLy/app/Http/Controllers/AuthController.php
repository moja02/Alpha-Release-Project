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
}
