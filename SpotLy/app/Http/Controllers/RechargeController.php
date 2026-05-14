<?php
namespace App\Http\Controllers;

use App\Models\RechargeRequest;
use App\Models\Wallet;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RechargeController extends Controller
{
    /**
     * جلب طلبات الشحن المعلقة
     */
    public function getPendingRecharges(Request $request)
    {
        try {
            $request->validate(['employeeId' => 'required|integer']);
            $inputEmployeeId = $request->input('employeeId');

            // جلب الطلبات الموجهة فقط للساحة المرتبطة بهذا الموظف
            $pendingRequests = \Illuminate\Support\Facades\DB::table('recharge_requests')
                ->join('accounts', 'recharge_requests.user_id', '=', 'accounts.id')
                ->join('parkings', 'recharge_requests.parking_id', '=', 'parkings.id')
                ->where('parkings.employee_id', $inputEmployeeId)
                ->where('recharge_requests.status', 'Pending')
                ->select('recharge_requests.*', 'accounts.name as user_name', 'parkings.name as parking_name')
                ->get();

            return response()->json(['status' => 'success', 'data' => $pendingRequests]);

        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    /**
     * تنفيذ الشحن الفوري المباشر من قبل الموظف
     */
    public function directRecharge(Request $request)
    {
        try {
            // التحقق من وجود الحساب المستهدف
            $request->validate([
                'userId' => 'required|exists:accounts,id',
                'amount' => 'required|integer|min:1'
            ]);

            $targetUserId = $request->input('userId');
            $rechargeAmount = $request->input('amount');

            \Illuminate\Support\Facades\DB::beginTransaction();

            //  البحث عن محفظة السائق، وإنشاؤها إن لم تكن موجودة
            $userWallet = \Illuminate\Support\Facades\DB::table('wallets')->where('user_id', $targetUserId)->first();

            if (!$userWallet) {
                \Illuminate\Support\Facades\DB::table('wallets')->insert([
                    'user_id' => $targetUserId,
                    'balance' => $rechargeAmount,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                \Illuminate\Support\Facades\DB::table('wallets')
                    ->where('user_id', $targetUserId)
                    ->increment('balance', $rechargeAmount);
            }

            // إرسال إشعار فوري للسائق بالشحن المباشر
            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'user_id' => $targetUserId,
                'message' => "تم شحن محفظتك بـ {$rechargeAmount} نقطة مباشرة من قبل الإدارة.",
                'type' => 'Direct_Recharge',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم شحن المحفظة بنجاح.'
            ], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error in direct recharge: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * معالجة واعتماد/رفض طلب الشحن
     */
    public function verifyRequest(Request $request)
    {
        try {
            // التحقق من صحة المدخلات
            $request->validate([
                'requestId' => 'required|exists:recharge_requests,id',
                'action' => 'required|in:approve,reject',
                'rejectionReason' => 'nullable|string'
            ]);

            $requestIdValue = $request->input('requestId');
            $actionType = $request->input('action');
            $rejectionReason = $request->input('rejectionReason');

            \Illuminate\Support\Facades\DB::beginTransaction();

            // جلب بيانات الطلب مع تأمين السجل للقراءة
            $rechargeRecord = \Illuminate\Support\Facades\DB::table('recharge_requests')
                ->where('id', $requestIdValue)
                ->lockForUpdate()
                ->first();

            // التأكد من أن الطلب لا يزال قيد المراجعة لمنع الاعتماد المزدوج
            if ($rechargeRecord->status !== 'Pending') {
                return response()->json(['status' => 'error', 'message' => 'هذا الطلب تمت معالجته مسبقاً.'], 400);
            }

            if ($actionType === 'approve') {
                // 1. تحديث رصيد المحفظة الخاص بالمستخدم
                $userWallet = \Illuminate\Support\Facades\DB::table('wallets')
                    ->where('user_id', $rechargeRecord->user_id)
                    ->first();

                if (!$userWallet) {
                    // إنشاء محفظة جديدة إذا لم تكن موجودة
                    \Illuminate\Support\Facades\DB::table('wallets')->insert([
                        'user_id' => $rechargeRecord->user_id,
                        'balance' => $rechargeRecord->requested_points,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                } else {
                    // زيادة الرصيد الحالي بمقدار النقاط المطلوبة في الطلب
                    \Illuminate\Support\Facades\DB::table('wallets')
                        ->where('user_id', $rechargeRecord->user_id)
                        ->increment('balance', $rechargeRecord->requested_points);
                }

                // 2. تحديث حالة الطلب إلى مقبول
                \Illuminate\Support\Facades\DB::table('recharge_requests')
                    ->where('id', $requestIdValue)
                    ->update([
                        'status' => 'Approved',
                        'updated_at' => now()
                    ]);

                // 3. إرسال إشعار بنجاح الشحن
                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'user_id' => $rechargeRecord->user_id,
                    'message' => "تهانينا! تم اعتماد إيصال التحويل الخاص بك وإضافة {$rechargeRecord->requested_points} نقطة لمحفظتك.",
                    'type' => 'Recharge_Approved',
                    'created_at' => now()
                ]);

            } else {
                // في حالة الرفض: تحديث الحالة فقط مع ذكر السبب (اختياري)
                \Illuminate\Support\Facades\DB::table('recharge_requests')
                    ->where('id', $requestIdValue)
                    ->update([
                        'status' => 'Rejected',
                        'updated_at' => now()
                    ]);

                \Illuminate\Support\Facades\DB::table('notifications')->insert([
                    'user_id' => $rechargeRecord->user_id,
                    'message' => "عذراً، تم رفض طلب الشحن الخاص بك. السبب: " . ($rejectionReason ?? 'البيانات غير واضحة'),
                    'type' => 'Recharge_Rejected',
                    'created_at' => now()
                ]);
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تمت معالجة الطلب وتحديث رصيد المحفظة بنجاح.'
            ], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error verifying recharge: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    public function getBalance(Request $request)
    {
        try {
            $request->validate([
                'userId' => 'required|integer'
            ]);

            $targetUserId = $request->input('userId');
            
            $walletData = \Illuminate\Support\Facades\DB::table('wallets')
                ->where('user_id', $targetUserId)
                ->first();
            
            return response()->json([
                'status' => 'success',
                'balance' => $walletData ? $walletData->balance : 0
            ], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error('Error fetching balance: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * إرسال طلب شحن موجه لساحة وقوف محددة
     */
    public function submitRequest(Request $request)
    {
        try {
            $request->validate([
                'userId' => 'required|integer',
                'parkingId' => 'required|integer|exists:parkings,id',
                'amount' => 'required|integer|min:5',
                'receipt' => 'required|image|max:2048'
            ]);

            $inputUserId = $request->input('userId');
            $inputParkingId = $request->input('parkingId');
            $inputAmount = $request->input('amount');
            
            // تخزين صورة الإيصال في المجلد العام
            $uploadedFilePath = $request->file('receipt')->store('receipts', 'public');

            // إدراج الطلب مع ربطه بالساحة المستهدفة
            \Illuminate\Support\Facades\DB::table('recharge_requests')->insert([
                'user_id' => $inputUserId,
                'parking_id' => $inputParkingId,
                'requested_points' => $inputAmount,
                'receipt_file' => $uploadedFilePath,
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['status' => 'success'], 201);

        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    public function getUserRechargeRequests(Request $request)
    {
        try {
            $request->validate([
                'userId' => 'required|integer'
            ]);

            $targetUserId = $request->input('userId');

            $userRequestsHistory = \Illuminate\Support\Facades\DB::table('recharge_requests')
                ->where('user_id', $targetUserId)
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $userRequestsHistory
            ], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error($exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 500);
        }
    }
}