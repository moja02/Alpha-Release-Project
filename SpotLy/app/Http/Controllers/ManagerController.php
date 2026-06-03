<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ManagerController extends Controller
{
    /**
     * عرض لوحة تحكم المدير
     */
    public function index()
    {
        try {
            $user = auth()->user();

            if (!$user || $user->role !== 'manager') {
                abort(403, 'غير مصرح لك بالدخول!');
            }

            // جلب سجل المدير من جدول managers
            $manager = DB::table('managers')->where('account_id', $user->id)->first();

            if (!$manager) {
                abort(403, 'لا يوجد ملف تعريف مدير مرتبط بهذا الحساب!');
            }

            // في حال تم حظر المدير، يتم تسجيل خروجه وتوجيهه لصفحة الدخول
            if ($manager->status === 'blocked') {
                Auth::guard('web')->logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();
                return redirect('/login')->with('error', 'تم حظر حسابك من قبل الإدارة!');
            }

            // قائمة الساحات المدارة مع تفاصيل الموظف المسؤول
            $parkingsList = DB::table('parkings')
                ->leftJoin('employees', 'parkings.employee_id', '=', 'employees.id')
                ->leftJoin('accounts', 'employees.account_id', '=', 'accounts.id')
                ->select(
                    'parkings.id',
                    'parkings.name as parking_name',
                    'parkings.location_park',
                    'parkings.total_capacity',
                    'parkings.available_capacity',
                    'accounts.name as employee_name',
                    'accounts.phone as employee_phone'
                )
                ->where('parkings.manager_id', $manager->id)
                ->get();

            return view('dashboards.manager', compact('parkingsList'));

        } catch (\Exception $exception) {
            abort(500, 'حدث خطأ داخلي أثناء تحميل لوحة تحكم المدير: ' . $exception->getMessage());
        }
    }
}
