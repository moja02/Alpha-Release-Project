<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Account;
use App\Models\Manager;
use App\Models\Parking;
use App\Models\Booking;
use App\Mail\SpotlyNotificationMail;

class DeveloperController extends Controller
{
    // 1. عرض لوحة التحكم الشاملة المحسنة
    public function index()
    {
        try {
            if (!in_array(auth()->user()->role, ['developer', 'manager', 'admin'])) {
                abort(403, 'غير مصرح لك!');
            }

            // 1. الإحصاءات العامة للمواقف والمدراء والحجوزات
            $parkingsCount = DB::table('parkings')->count();
            $employeesCount = DB::table('employees')->count();
            $managersCount = DB::table('managers')->where('status', 'active')->count();
            $activeBookingsCount = DB::table('bookings')
                ->where('status', 'confirmed')
                ->where('end_time', '>', Carbon::now())
                ->count();

            // 2. الإحصاءات المالية الإجمالية
            $totalRevenue = (float) DB::table('bookings')->where('status', 'confirmed')->sum('cost');
            $totalRefunds = (float) DB::table('bookings')->sum('refund_amount');
            $netRevenue = $totalRevenue - $totalRefunds;

            // 3. نسبة الإشغال الكلية
            $totalCapacity = (int) DB::table('parkings')->sum('total_capacity');
            $availableCapacity = (int) DB::table('parkings')->sum('available_capacity');
            $occupiedSpots = max(0, $totalCapacity - $availableCapacity);
            $occupancyRate = $totalCapacity > 0 ? round(($occupiedSpots / $totalCapacity) * 100, 1) : 0;

            // 4. جلب الساحات المكتملة مع علاقاتها
            $parkingsList = Parking::with(['manager.account', 'employee'])->get();

            // 5. جلب موظفي الميدان
            $employeesList = DB::table('accounts')
                ->join('employees', 'accounts.id', '=', 'employees.account_id')
                ->leftJoin('parkings', 'employees.id', '=', 'parkings.employee_id')
                ->select(
                    'accounts.id as account_id', 
                    'employees.id as employee_id', 
                    'accounts.name', 
                    'accounts.email', 
                    'accounts.phone', 
                    'employees.bank_account_number', 
                    'accounts.created_at',
                    'parkings.name as parking_name'
                )
                ->get();

            // 6. جلب المدراء مع علاقاتهم
            $managers = Manager::with(['account', 'parkings'])->get();

            // 7. تحليلات الاتجاه المالي (شهرياً ويومياً) للرسوم البيانية
            $bookingsQuery = DB::table('bookings')->get();

            $monthlyFinancialTrends = $bookingsQuery->groupBy(function ($item) {
                return Carbon::parse($item->start_time)->format('Y-m');
            })->map(function ($items, $month) {
                $gross = $items->where('status', 'confirmed')->sum('cost');
                $refunds = $items->sum('refund_amount');
                return [
                    'month' => $month,
                    'revenue' => (float)$gross,
                    'refunds' => (float)$refunds,
                    'net' => (float)($gross - $refunds)
                ];
            })->values()->sortBy('month')->values()->all();

            $dailyFinancialTrends = $bookingsQuery->filter(function ($item) {
                return Carbon::parse($item->start_time)->gte(Carbon::now()->subDays(14));
            })->groupBy(function ($item) {
                return Carbon::parse($item->start_time)->format('Y-m-d');
            })->map(function ($items, $date) {
                $gross = $items->where('status', 'confirmed')->sum('cost');
                $refunds = $items->sum('refund_amount');
                return [
                    'date' => $date,
                    'revenue' => (float)$gross,
                    'refunds' => (float)$refunds,
                    'net' => (float)($gross - $refunds)
                ];
            })->values()->sortBy('date')->values()->all();

            // 8. مقارنة أداء الفروع تفصيلياً
            $branchPerformance = $parkingsList->map(function ($p) use ($bookingsQuery) {
                $pBookings = $bookingsQuery->where('parking_id', $p->id);
                $gross = $pBookings->where('status', 'confirmed')->sum('cost');
                $refunds = $pBookings->sum('refund_amount');
                $occupied = max(0, $p->total_capacity - $p->available_capacity);
                $occRate = $p->total_capacity > 0 ? round(($occupied / $p->total_capacity) * 100, 1) : 0;
                
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'location' => $p->location_park,
                    'total_capacity' => $p->total_capacity,
                    'available_capacity' => $p->available_capacity,
                    'occupied_spots' => $occupied,
                    'occupancy_rate' => $occRate,
                    'gross_revenue' => (float)$gross,
                    'refunds' => (float)$refunds,
                    'net_revenue' => (float)($gross - $refunds),
                    'bookings_count' => $pBookings->count(),
                    'manager_name' => ($p->manager && $p->manager->account) ? $p->manager->account->name : 'غير معين'
                ];
            });

            return view('developer.developer_dashboard', compact(
                'parkingsCount',
                'employeesCount',
                'managersCount',
                'activeBookingsCount',
                'totalRevenue',
                'totalRefunds',
                'netRevenue',
                'totalCapacity',
                'availableCapacity',
                'occupiedSpots',
                'occupancyRate',
                'managers',
                'parkingsList',
                'employeesList',
                'monthlyFinancialTrends',
                'dailyFinancialTrends',
                'branchPerformance'
            ));
        } catch (\Exception $exception) {
            Log::error('Error loading developer index view: ' . $exception->getMessage());
            abort(500, 'حدث خطأ داخلي أثناء تحميل اللوحة: ' . $exception->getMessage());
        }
    }

    // 2. استقبال بيانات الخريطة وحفظ الموقف الجديد
    public function storeParking(Request $request)
    {
        if (!in_array(auth()->user()->role, ['developer', 'manager'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك!'
            ], 403);
        }

        try {
            // التحقق من صحة البيانات 
            $request->validate([
                'name' => 'required|string|max:191',
                'location_park' => 'required|string|max:191',
                'total_capacity' => 'required|integer|min:1',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            // إدراج الموقف في قاعدة البيانات
            $insertedId = \Illuminate\Support\Facades\DB::table('parkings')->insertGetId([
                'name' => $request->name,
                'location_park' => $request->location_park, // إدراج الوصف/الموقع
                'total_capacity' => $request->total_capacity,
                'available_capacity' => $request->total_capacity, // الشواغر المبدئية
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'employee_id' => null, // ترك الموظف فارغاً لتعيينه لاحقاً 
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            return response()->json([
                'status' => 'success', 
                'message' => 'تم حفظ الموقف بنجاح وتعيين شواغره.',
                'parking' => [
                    'id' => $insertedId,
                    'name' => $request->name,
                    'total_capacity' => $request->total_capacity,
                    'available_capacity' => $request->total_capacity,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }

    // 3. إنشاء حساب مدير جديد
    public function storeManager(Request $request)
    {
        if (!in_array(auth()->user()->role, ['developer', 'manager'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك!'
            ], 403);
        }

        try {
            // التحقق من صحة البيانات 
            $request->validate([
                'name' => 'required|string|max:191',
                'email' => 'required|string|email|max:191|unique:accounts,email',
                'phone' => 'required|string|max:20',
                'password' => 'required|string|min:6',
                'role' => 'nullable|string|in:manager,admin,developer'
            ]);

            $targetRole = $request->input('role', 'manager');

            DB::beginTransaction();

            // إدراج الحساب في قاعدة البيانات
            $insertedId = DB::table('accounts')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => \Illuminate\Support\Facades\Hash::make($request->password),
                'role' => $targetRole,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            // إدراج سجل في جدول managers
            $managerProfileId = DB::table('managers')->insertGetId([
                'account_id' => $insertedId,
                'status' => 'active',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);

            DB::commit();

            // إرسال بريد إلكتروني للحساب الجديد ببيانات الدخول
            try {
                $mailData = [
                    'title' => '🔑 بيانات الحساب الجديد - SpotLy',
                    'body' => "مرحباً {$request->name}،\n\n" .
                              "تم إنشاء حساب جديد لك في منصة SpotLy بدور ({$targetRole}).\n\n" .
                              "البريد الإلكتروني: {$request->email}\n" .
                              "كلمة المرور: {$request->password}\n\n" .
                              "يرجى تسجيل الدخول وإدارة حسابك عبر البوابة."
                ];
                Mail::to($request->email)->send(new SpotlyNotificationMail($mailData));
            } catch (\Exception $mailEx) {
                Log::error('فشل إرسال بريد إنشاء الحساب في storeManager: ' . $mailEx->getMessage());
            }

            return response()->json([
                'status' => 'success', 
                'message' => 'تم إنشاء حساب المدير بنجاح.',
                'manager' => [
                    'id' => $insertedId,
                    'manager_id' => $managerProfileId,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'status' => 'active',
                    'created_at' => \Carbon\Carbon::now()->format('Y-m-d H:i')
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error', 
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }

    // 4. تغيير حالة تفعيل المدير (تنشيط / تعطيل)
    public function toggleManagerStatus($id)
    {
        if (!in_array(auth()->user()->role, ['developer', 'manager'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك!'
            ], 403);
        }

        try {
            // التحقق من وجود الحساب
            $account = DB::table('accounts')->where('id', $id)->where('role', 'manager')->first();
            if (!$account) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'حساب المدير غير موجود.'
                ], 404);
            }

            // التحقق من وجود سجل في جدول managers
            $managerProfile = DB::table('managers')->where('account_id', $id)->first();
            if (!$managerProfile) {
                DB::table('managers')->insert([
                    'account_id' => $id,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $currentStatus = 'active';
            } else {
                $currentStatus = $managerProfile->status;
            }

            $newStatus = ($currentStatus === 'active') ? 'blocked' : 'active';

            DB::table('managers')
                ->where('account_id', $id)
                ->update([
                    'status' => $newStatus,
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => 'success',
                'message' => ($newStatus === 'active') ? 'تم تفعيل حساب المدير بنجاح.' : 'تم تعطيل حساب المدير بنجاح.',
                'new_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }

    // 5. جلب ساحات المدير والساحات غير المربوطة
    public function getManagerParkings($id)
    {
        if (!in_array(auth()->user()->role, ['developer', 'manager'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك!'
            ], 403);
        }

        try {
            // البحث عن حساب المدير
            $account = DB::table('accounts')->where('id', $id)->where('role', 'manager')->first();
            if (!$account) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'حساب المدير غير موجود.'
                ], 404);
            }

            // البحث عن سجل المدير في جدول managers
            $manager = DB::table('managers')->where('account_id', $id)->first();
            if (!$manager) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'سجل المدير غير موجود.'
                ], 404);
            }

            // جلب الساحات المربوطة بهذا المدير + الساحات غير المربوطة بأي مدير
            $parkings = DB::table('parkings')
                ->select('id', 'name', 'location_park', 'manager_id')
                ->whereNull('manager_id')
                ->orWhere('manager_id', $manager->id)
                ->get();

            return response()->json([
                'status' => 'success',
                'manager_name' => $account->name,
                'manager_id' => $manager->id,
                'parkings' => $parkings
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }

    // 6. تحديث ساحات المدير
    public function updateManagerParkings(Request $request, $id)
    {
        if (!in_array(auth()->user()->role, ['developer', 'manager'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك!'
            ], 403);
        }

        try {
            $request->validate([
                'parking_ids' => 'nullable|array',
                'parking_ids.*' => 'integer|exists:parkings,id'
            ]);

            // البحث عن سجل المدير في جدول managers
            $manager = DB::table('managers')->where('account_id', $id)->first();
            if (!$manager) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'سجل المدير غير موجود.'
                ], 404);
            }

            $parkingIds = $request->input('parking_ids', []);

            DB::beginTransaction();

            // إزالة ربط الساحات التي كانت تابعة لهذا المدير
            DB::table('parkings')
                ->where('manager_id', $manager->id)
                ->update([
                    'manager_id' => null,
                    'updated_at' => now()
                ]);

            // ربط الساحات الجديدة بهذا المدير
            if (!empty($parkingIds)) {
                DB::table('parkings')
                    ->whereIn('id', $parkingIds)
                    ->update([
                        'manager_id' => $manager->id,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث ربط الساحات بالمدير بنجاح.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 7. جلب مؤشرات لوحة التحكم المجمعة وإحصاءات النظام
     */
    public function dashboardStatistics()
    {
        try {
            $stats = Cache::remember('super_admin_dashboard_stats', 900, function () {
                $totalRevenue = DB::table('bookings')->where('status', 'confirmed')->sum('cost');
                $totalRefunds = DB::table('bookings')->sum('refund_amount');
                $activeBookings = DB::table('bookings')
                    ->where('status', 'confirmed')
                    ->where('end_time', '>', Carbon::now())
                    ->count();

                $totalCapacity = DB::table('parkings')->sum('total_capacity');
                $availableCapacity = DB::table('parkings')->sum('available_capacity');
                $occupiedSpots = $totalCapacity - $availableCapacity;

                return [
                    'summary' => [
                        'total_revenue' => (float)$totalRevenue,
                        'total_refunds' => (float)$totalRefunds,
                        'net_revenue' => (float)($totalRevenue - $totalRefunds),
                        'active_bookings_count' => $activeBookings,
                        'total_parking_lots' => DB::table('parkings')->count(),
                        'total_active_managers' => DB::table('managers')->where('status', 'active')->count()
                    ],
                    'occupancy' => [
                        'global_capacity' => (int)$totalCapacity,
                        'occupied_spots' => (int)$occupiedSpots,
                        'occupancy_rate_percentage' => $totalCapacity > 0 ? round(($occupiedSpots / $totalCapacity) * 100, 2) : 0
                    ]
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ], 200);

        } catch (\Exception $exception) {
            Log::error('Error fetching dashboard statistics in DeveloperController: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء جلب إحصاءات لوحة التحكم.'
            ], 500);
        }
    }

    /**
     * 8. جلب التقارير المالية التفصيلية وحساب صافي الإيرادات حسب الفرع والاتجاه الشهري
     */
    public function financialReports(Request $request)
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $parkingId = $request->input('parking_id');

            $query = DB::table('bookings')
                ->join('parkings', 'bookings.parking_id', '=', 'parkings.id')
                ->select(
                    'bookings.*',
                    'parkings.name as parking_name'
                );

            if ($startDate) {
                $query->where('bookings.start_time', '>=', Carbon::parse($startDate)->startOfDay());
            }
            if ($endDate) {
                $query->where('bookings.start_time', '<=', Carbon::parse($endDate)->endOfDay());
            }
            if ($parkingId) {
                $query->where('bookings.parking_id', $parkingId);
            }

            $bookings = $query->get();

            $totalEarnings = $bookings->where('status', 'confirmed')->sum('cost');
            $refundedEarnings = $bookings->sum('refund_amount');
            $netEarnings = $totalEarnings - $refundedEarnings;

            $revenueByParking = $bookings->groupBy('parking_id')->map(function ($items, $parkingId) {
                $firstItem = $items->first();
                $gross = $items->where('status', 'confirmed')->sum('cost');
                $refunds = $items->sum('refund_amount');
                return [
                    'parking_id' => (int)$parkingId,
                    'parking_name' => $firstItem->parking_name,
                    'bookings_count' => $items->count(),
                    'gross_revenue' => (float)$gross,
                    'refunds' => (float)$refunds,
                    'net_revenue' => (float)($gross - $refunds)
                ];
            })->values()->all();

            $monthlyTrend = $bookings->groupBy(function ($item) {
                return Carbon::parse($item->start_time)->format('Y-m');
            })->map(function ($items, $month) {
                $gross = $items->where('status', 'confirmed')->sum('cost');
                $refunds = $items->sum('refund_amount');
                return [
                    'month' => $month,
                    'revenue' => (float)($gross - $refunds)
                ];
            })->values()->sortBy('month')->values()->all();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'filter' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'parking_id' => $parkingId ? (int)$parkingId : null
                    ],
                    'metrics' => [
                        'total_earnings' => (float)$totalEarnings,
                        'refunded_earnings' => (float)$refundedEarnings,
                        'net_earnings' => (float)$netEarnings
                    ],
                    'revenue_by_parking' => $revenueByParking,
                    'monthly_trend' => $monthlyTrend
                ]
            ], 200);

        } catch (\Exception $exception) {
            Log::error('Error fetching financial reports in DeveloperController: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء جلب التقارير المالية.'
            ], 500);
        }
    }

    /**
     * 9. تعديل وتحديث بيانات موقف السيارات مع التحقق من سلامة السعة الاستيعابية
     */
    public function updateParking(Request $request, $id)
    {
        $parking = Parking::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'location_park' => 'sometimes|required|string',
            'total_capacity' => 'sometimes|required|integer|min:1',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'manager_id' => 'nullable|exists:managers,id'
        ]);

        try {
            if (isset($validated['total_capacity'])) {
                $occupied = $parking->total_capacity - $parking->available_capacity;

                if ($validated['total_capacity'] < $occupied) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'لا يمكن خفض السعة الإجمالية للموقف لتصبح أقل من عدد السيارات المتوقفة حالياً بالفعل.'
                    ], 422);
                }

                $validated['available_capacity'] = $validated['total_capacity'] - $occupied;
            }

            $parking->update($validated);

            Cache::forget('super_admin_dashboard_stats');

            return response()->json([
                'status' => 'success',
                'message' => 'تم تعديل موقف السيارات بنجاح.',
                'data' => $parking
            ], 200);

        } catch (\Exception $exception) {
            Log::error('Error updating parking in DeveloperController: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() ?: 'حدث خطأ أثناء تعديل موقف السيارات.'
            ], 500);
        }
    }

    /**
     * 10. حذف موقف سيارات مع التحقق من عدم وجود حجوزات جارية
     */
    public function deleteParking($id)
    {
        if (!in_array(auth()->user()->role, ['developer', 'manager', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك!'
            ], 403);
        }

        try {
            $parking = Parking::find($id);
            if (!$parking) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'موقف السيارات غير موجود.'
                ], 404);
            }

            $hasActiveBookings = Booking::where('parking_id', $id)
                ->where('status', 'confirmed')
                ->where('end_time', '>', Carbon::now())
                ->exists();

            if ($hasActiveBookings) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لا يمكن حذف موقف السيارات لوجود حجوزات جارية نشطة فيه حالياً.'
                ], 400);
            }

            DB::beginTransaction();

            $parking->delete();

            DB::commit();

            Cache::forget('super_admin_dashboard_stats');

            return response()->json([
                'status' => 'success',
                'message' => 'تم حذف موقف السيارات بنجاح.'
            ], 200);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error deleting parking in DeveloperController: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء حذف موقف السيارات: ' . $exception->getMessage()
            ], 500);
        }
    }

    /**
     * 11. حذف مدير نظام من قاعدة البيانات وعلاقاته
     */
    public function deleteManager($id)
    {
        if (!in_array(auth()->user()->role, ['developer', 'manager', 'admin'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك!'
            ], 403);
        }

        try {
            DB::beginTransaction();

            // البحث عن سجل المدير (إما بـ manager.id أو manager.account_id)
            $manager = Manager::where('id', $id)->orWhere('account_id', $id)->first();

            if ($manager) {
                // إلغاء ربط أي مواقف سيارات مسندة لهذا المدير لتجنب مشاكل القيود المرجعية
                DB::table('parkings')->where('manager_id', $manager->id)->update(['manager_id' => null]);

                $accountId = $manager->account_id;

                // حذف سجل المدير من جدول managers
                $manager->delete();

                // حذف حساب المدير من جدول accounts
                $account = Account::find($accountId);
                if ($account) {
                    $account->delete();
                }
            } else {
                // في حال عدم وجود سجل في جدول managers، البحث عن الحساب في جدول accounts بالدور manager
                $account = Account::where('id', $id)->where('role', 'manager')->first();
                if ($account) {
                    $account->delete();
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'حساب المدير غير موجود.'
                    ], 404);
                }
            }

            DB::commit();

            Cache::forget('super_admin_dashboard_stats');

            return response()->json([
                'status' => 'success',
                'message' => 'تم حذف المدير بنجاح.'
            ], 200);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error deleting manager in DeveloperController: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء حذف المدير.'
            ], 500);
        }
    }

    /**
     * 12. عرض جميع مواقف السيارات
     */
    public function listParkings()
    {
        try {
            $parkings = Parking::with(['manager.account'])->get();

            $formatted = $parkings->map(function ($parking) {
                return [
                    'id' => $parking->id,
                    'name' => $parking->name,
                    'location_park' => $parking->location_park,
                    'total_capacity' => $parking->total_capacity,
                    'available_capacity' => $parking->available_capacity,
                    'latitude' => $parking->latitude ? (float)$parking->latitude : null,
                    'longitude' => $parking->longitude ? (float)$parking->longitude : null,
                    'employee_id' => $parking->employee_id,
                    'manager' => $parking->manager ? [
                        'id' => $parking->manager->id,
                        'name' => $parking->manager->account->name,
                        'email' => $parking->manager->account->email
                    ] : null,
                    'created_at' => $parking->created_at
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formatted
            ], 200);

        } catch (\Exception $exception) {
            Log::error('Error listing parkings: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء جلب مواقف السيارات.'
            ], 500);
        }
    }

    /**
     * 13. إضافة موقف سيارات جديد
     */
    public function createParking(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location_park' => 'required|string',
            'total_capacity' => 'required|integer|min:1',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'manager_id' => 'nullable|exists:managers,id'
        ]);

        try {
            $validated['available_capacity'] = $validated['total_capacity'];

            $parking = Parking::create($validated);

            Cache::forget('super_admin_dashboard_stats');

            return response()->json([
                'status' => 'success',
                'message' => 'Parking lot created successfully.',
                'data' => $parking
            ], 201);

        } catch (\Exception $exception) {
            Log::error('Error creating parking: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إنشاء موقف السيارات.'
            ], 500);
        }
    }

    /**
     * 14. عرض قائمة المدراء
     */
    public function listManagers()
    {
        try {
            $managers = Manager::with(['account', 'parkings'])->get();

            $formatted = $managers->map(function ($manager) {
                return [
                    'manager_id' => $manager->id,
                    'account_id' => $manager->account_id,
                    'name' => $manager->account->name,
                    'email' => $manager->account->email,
                    'phone' => $manager->account->phone,
                    'status' => $manager->status,
                    'managed_parkings' => $manager->parkings->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name,
                            'total_capacity' => $p->total_capacity
                        ];
                    })
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $formatted
            ], 200);

        } catch (\Exception $exception) {
            Log::error('Error listing managers: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء جلب قائمة المدراء.'
            ], 500);
        }
    }

    /**
     * 15. إنشاء مدير نظام جديد
     */
    public function createManager(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:accounts,email',
            'phone' => 'required|string',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:manager,admin,developer'
        ]);

        $targetRole = $request->input('role', 'manager');

        try {
            DB::beginTransaction();

            $account = Account::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => $targetRole
            ]);

            $manager = Manager::create([
                'account_id' => $account->id,
                'status' => 'active'
            ]);

            DB::commit();

            // إرسال بريد إلكتروني للحساب الجديد ببيانات الدخول
            try {
                $mailData = [
                    'title' => '🔑 بيانات الحساب الجديد - SpotLy',
                    'body' => "مرحباً {$validated['name']}،\n\n" .
                              "تم إنشاء حساب جديد لك في منصة SpotLy بدور ({$targetRole}).\n\n" .
                              "البريد الإلكتروني: {$validated['email']}\n" .
                              "كلمة المرور: {$validated['password']}\n\n" .
                              "يرجى تسجيل الدخول وإدارة حسابك عبر البوابة."
                ];
                Mail::to($validated['email'])->send(new SpotlyNotificationMail($mailData));
            } catch (\Exception $mailEx) {
                Log::error('فشل إرسال بريد إنشاء الحساب في createManager: ' . $mailEx->getMessage());
            }

            Cache::forget('super_admin_dashboard_stats');

            return response()->json([
                'status' => 'success',
                'message' => 'Manager created successfully.',
                'data' => [
                    'manager_id' => $manager->id,
                    'account_id' => $account->id,
                    'name' => $account->name,
                    'email' => $account->email,
                    'phone' => $account->phone,
                    'status' => $manager->status,
                    'created_at' => $manager->created_at
                ]
            ], 201);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error creating manager: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إنشاء حساب المدير.'
            ], 500);
        }
    }

    /**
     * 16. تعديل حالة مدير
     */
    public function updateManagerStatus(Request $request, $id)
    {
        $manager = Manager::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:active,blocked'
        ]);

        try {
            $manager->update([
                'status' => $validated['status']
            ]);

            Cache::forget('super_admin_dashboard_stats');

            return response()->json([
                'status' => 'success',
                'message' => 'Manager status updated successfully.'
            ], 200);

        } catch (\Exception $exception) {
            Log::error('Error updating manager status: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء تعديل حالة حساب المدير.'
            ], 500);
        }
    }

    /**
     * 17. تعيين مدير لمجموعة من مواقف السيارات
     */
    public function assignParking(Request $request, $id)
    {
        $manager = Manager::findOrFail($id);

        $validated = $request->validate([
            'parking_ids' => 'required|array',
            'parking_ids.*' => 'exists:parkings,id'
        ]);

        try {
            DB::beginTransaction();

            Parking::whereIn('id', $validated['parking_ids'])
                ->update(['manager_id' => $manager->id]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Manager assigned to the specified parking lots successfully.',
                'data' => [
                    'manager_id' => $manager->id,
                    'assigned_parkings' => Parking::where('manager_id', $manager->id)->get(['id', 'name'])
                ]
            ], 200);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error assigning manager: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء تعيين المدير لمواقف السيارات.'
            ], 500);
        }
    }

    /**
     * 18. إعادة تعيين إدارة المواقف (Reassign Manager)
     */
    public function reassignParking(Request $request, $id)
    {
        $manager = Manager::findOrFail($id);

        $validated = $request->validate([
            'parking_ids' => 'required|array',
            'parking_ids.*' => 'exists:parkings,id'
        ]);

        try {
            DB::beginTransaction();

            Parking::where('manager_id', $manager->id)
                ->update(['manager_id' => null]);

            Parking::whereIn('id', $validated['parking_ids'])
                ->update(['manager_id' => $manager->id]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Parking reassignment completed successfully.',
                'data' => [
                    'manager_id' => $manager->id,
                    'assigned_parkings' => Parking::where('manager_id', $manager->id)->get(['id', 'name'])
                ]
            ], 200);

        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Error reassigning manager: ' . $exception->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إعادة تعيين مواقف السيارات.'
            ], 500);
        }
    }

    /**
     * توليد التقرير المالي للذكاء الاصطناعي بناءً على بيانات النظام الحقيقية
     */
    public function generateAiFinancialReport(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || !in_array($user->role, ['developer', 'manager', 'admin'])) {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $scope = $request->query('scope', 'all');
            $timeframe = $request->query('timeframe', 'current_quarter');

            $service = new \App\Services\AiFinancialAnalystService();
            $reportData = $service->generateReport(null, $scope, $timeframe);

            return response()->json($reportData);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطأ أثناء توليد التقرير الذكي: ' . $e->getMessage()], 500);
        }
    }

    /**
     * معالجة محادثة المساعد الذكي للأدمن
     */
    public function aiChat(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || !in_array($user->role, ['developer', 'manager', 'admin'])) {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $message = $request->input('message', '');
            $history = $request->input('history', []);

            if (empty(trim($message))) {
                return response()->json(['status' => 'error', 'message' => 'يرجى إدخال السؤال.'], 422);
            }

            $chatService = new \App\Services\AiChatAssistantService();
            $result = $chatService->ask($message, $history, null);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'حدث خطأ في المساعد الذكي: ' . $e->getMessage()], 500);
        }
    }
}