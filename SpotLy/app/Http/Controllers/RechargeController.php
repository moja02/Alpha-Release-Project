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
    public function getPendingRecharges()
    {
        try {
            $pendingRequests = RechargeRequest::with('user')->where('status', 'pending')->get();
            return response()->json(['status' => 'success', 'data' => $pendingRequests]);
        } catch (\Exception $exception) {
            Log::error('Error getting recharges: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
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

    public function submitRequest(Request $request)
    {
        try {
            $request->validate([
                'userId' => 'required|integer',
                'amount' => 'required|integer|min:5',
                'receipt' => 'required|image|max:2048'
            ]);

            $inputUserId = $request->input('userId');
            $inputAmount = $request->input('amount');
            
            $uploadedFilePath = $request->file('receipt')->store('receipts', 'public');

            \Illuminate\Support\Facades\DB::table('recharge_requests')->insert([
                'user_id' => $inputUserId,
                'requested_points' => $inputAmount,
                'receipt_file' => $uploadedFilePath,
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success'
            ], 201);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error('Error submitting recharge request: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error', 
                'message' => $exception->getMessage()
            ], 500);
        }
    }
}