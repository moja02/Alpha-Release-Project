<?php
namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * دالة تحديث البيانات الشخصية
     */
    public function updateProfile(Request $request)
    {
        try {
            $request->validate([
                'accountId' => 'required|integer|exists:accounts,id',
                'phone' => 'required|string|max:20',
                'password' => 'nullable|string|min:6',
                'bankAccountNumber' => 'nullable|string|max:50',
            ]);

            $targetId = $request->input('accountId');
            
            DB::beginTransaction();

            // تعليق مضمن: تحديث بيانات جدول الحسابات الأساسي
            $account = Account::findOrFail($targetId);
            $account->phone = $request->input('phone');
            
            if ($request->filled('password')) {
                $account->password = Hash::make($request->input('password'));
            }
            $account->save();

            // تحديث الحساب البنكي للموظف إن وجد
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
            Log::error('Error in ProfileController: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
}