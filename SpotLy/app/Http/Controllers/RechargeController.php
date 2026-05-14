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

            // تعليق مضمن: البحث عن محفظة السائق، وإنشاؤها إن لم تكن موجودة
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
    public function verifyRechargeRequest(Request $request)
    {
        try {
            $request->validate([
                'requestId' => 'required|exists:recharge_requests,id',
                'action' => 'required|in:approve,reject',
                'rejectionReason' => 'required_if:action,reject'
            ]);

            $rechargeRequest = RechargeRequest::findOrFail($request->input('requestId'));
            DB::beginTransaction();

            if ($request->input('action') === 'approve') {
                $rechargeRequest->status = 'approved';
                
                // تعليق مضمن: إضافة الرصيد للمحفظة
                $userWallet = Wallet::firstOrCreate(
                    ['user_id' => $rechargeRequest->user_id],
                    ['balance' => 0]
                );
                $userWallet->balance += $rechargeRequest->amount;
                $userWallet->save();

                Notification::create([
                    'user_id' => $rechargeRequest->user_id,
                    'message' => "تم اعتماد طلب الشحن وإضافة {$rechargeRequest->amount} نقطة لمحفظتك.",
                    'type' => 'Recharge_Approved'
                ]);
            } else {
                $rechargeRequest->status = 'rejected';
                $rechargeRequest->rejection_reason = $request->input('rejectionReason');
            }

            $rechargeRequest->save();
            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'تمت المعالجة بنجاح.']);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error verifying recharge: ' . $exception->getMessage());
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