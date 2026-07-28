<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Mail\SpotlyNotificationMail;

class ManagerController extends Controller
{
    /**
     * 1. عرض لوحة تحكم المدير وتصفية الإحصائيات بناءً على ساحات المدير (Dashboard Isolation)
     */
    public function index(Request $request)
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

            // تصفية الساحات المدارة بحسب parking_id إن وجد أو كافة ساحات المدير
            $parkingsQuery = DB::table('parkings')
                ->leftJoin('employees', 'parkings.employee_id', '=', 'employees.id')
                ->leftJoin('accounts', 'employees.account_id', '=', 'accounts.id')
                ->select(
                    'parkings.id',
                    'parkings.name as parking_name',
                    'parkings.location_park',
                    'parkings.total_capacity',
                    'parkings.available_capacity',
                    'parkings.latitude',
                    'parkings.longitude',
                    'parkings.employee_id',
                    'accounts.name as employee_name',
                    'accounts.phone as employee_phone'
                )
                ->where('parkings.manager_id', $manager->id);

            if ($request->filled('parking_id')) {
                $parkingsQuery->where('parkings.id', $request->parking_id);
            }

            $parkingsList = $parkingsQuery->get()->map(function ($p) {
                $p->staff = DB::table('employees')
                    ->join('accounts', 'employees.account_id', '=', 'accounts.id')
                    ->where('employees.parking_id', $p->id)
                    ->orWhere('employees.id', $p->employee_id)
                    ->select('employees.id as employee_id', 'accounts.name as employee_name', 'accounts.phone as employee_phone', 'employees.shift_role')
                    ->distinct()
                    ->get();
                return $p;
            });
            $managerParkingIds = $parkingsList->pluck('id')->toArray();

            // قائمة الموظفين غير المعينين لأي ساحة وقوف حالياً
            $unassignedEmployees = DB::table('employees')
                ->join('accounts', 'employees.account_id', '=', 'accounts.id')
                ->whereNull('employees.parking_id')
                ->select(
                    'employees.id',
                    'accounts.name as employee_name',
                    'accounts.phone as employee_phone',
                    'employees.shift_role'
                )
                ->get();

            // قائمة السائقين (المستخدمين) المحظورين مع إجمالي الحجوزات
            $blockedUsers = DB::table('users')
                ->join('accounts', 'users.account_id', '=', 'accounts.id')
                ->select(
                    'users.id',
                    'users.account_id',
                    'accounts.name as driver_name',
                    'accounts.email as driver_email',
                    'accounts.phone as driver_phone',
                    'users.plate_number',
                    'users.fake_booking_count',
                    'users.status'
                )
                ->where('users.status', 'blocked')
                ->get()
                ->map(function ($driver) {
                    $driver->total_bookings = DB::table('bookings')
                        ->where('user_id', $driver->account_id)
                        ->orWhere('user_id', $driver->id)
                        ->count();
                    $driver->successful_bookings = DB::table('bookings')
                        ->where(function($q) use ($driver) {
                            $q->where('user_id', $driver->account_id)->orWhere('user_id', $driver->id);
                        })
                        ->whereIn('status', ['confirmed', 'completed'])
                        ->count();
                    return $driver;
                });

            // جلب بيانات التقرير المالي المعزولة بساحات المدير الحالي
            $financialReportService = new \App\Services\FinancialReportService();
            $financialData = $financialReportService->getFinancialReportData($manager->id);

            // قائمة الموظفين التابعين للمدير لغرض فلترة سجل العمليات والتدقيق
            $managerEmployees = DB::table('employees')
                ->join('accounts', 'employees.account_id', '=', 'accounts.id')
                ->where(function($q) use ($managerParkingIds, $manager) {
                    $q->whereIn('employees.id', function ($query) use ($managerParkingIds) {
                        $query->select('employee_id')
                              ->from('activity_cash_audit_logs')
                              ->whereIn('parking_id', $managerParkingIds);
                    })
                    ->orWhereIn('employees.id', function ($query) use ($manager) {
                        $query->select('employee_id')
                              ->from('parkings')
                              ->where('manager_id', $manager->id)
                              ->whereNotNull('employee_id');
                    });
                })
                ->select('employees.id', 'accounts.name as employee_name')
                ->distinct()
                ->get();

            // جلب إحصاءات وسجل مناوبات الموظفين الميدانيين اللحظية لمدير الساحات
            $activeShiftsCount = DB::table('employee_shifts')
                ->whereIn('employee_id', function($q) use ($managerParkingIds) {
                    $q->select('employee_id')->from('parkings')->whereIn('id', $managerParkingIds)->whereNotNull('employee_id');
                })
                ->where('status', 'active')
                ->count();

            $todayCompletedShiftsCount = DB::table('employee_shifts')
                ->whereIn('employee_id', function($q) use ($managerParkingIds) {
                    $q->select('employee_id')->from('parkings')->whereIn('id', $managerParkingIds)->whereNotNull('employee_id');
                })
                ->where('status', 'completed')
                ->whereDate('created_at', Carbon::today())
                ->count();

            $recentShiftsList = DB::table('employee_shifts')
                ->join('employees', 'employee_shifts.employee_id', '=', 'employees.id')
                ->join('accounts', 'employees.account_id', '=', 'accounts.id')
                ->leftJoin('parkings', 'employees.id', '=', 'parkings.employee_id')
                ->whereIn('parkings.id', $managerParkingIds)
                ->select(
                    'employee_shifts.*',
                    'accounts.name as employee_name',
                    'accounts.phone as employee_phone',
                    'parkings.name as parking_name'
                )
                ->orderBy('employee_shifts.created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($shift) {
                    $clockIn = Carbon::parse($shift->clock_in_at);
                    $clockOut = $shift->clock_out_at ? Carbon::parse($shift->clock_out_at) : null;
                    
                    $duration = 'مناوبة جارية 🟢';
                    if ($clockOut) {
                        $mins = $clockOut->diffInMinutes($clockIn);
                        $duration = floor($mins / 60) . 'س و ' . ($mins % 60) . 'د';
                    }

                    $shift->clock_in_formatted = $clockIn->format('Y-m-d H:i');
                    $shift->clock_out_formatted = $clockOut ? $clockOut->format('Y-m-d H:i') : '-';
                    $shift->duration_label = $duration;
                    return $shift;
                });

            return view('dashboards.manager', compact('parkingsList', 'unassignedEmployees', 'blockedUsers', 'financialData', 'managerEmployees', 'activeShiftsCount', 'todayCompletedShiftsCount', 'recentShiftsList'));

        } catch (\Exception $exception) {
            abort(500, 'حدث خطأ داخلي أثناء تحميل لوحة تحكم المدير: ' . $exception->getMessage());
        }
    }

    /**
     * 2. خوارزمية تتبع وتسجيل المخالفات والتأكد من إتمام حجزين ناجحين مسبقاً قبل تفعيل العد للحظر
     */
    public function handleViolation(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'reason' => 'nullable|string|max:255'
            ]);

            $targetUser = DB::table('users')->where('id', $request->user_id)->first();
            if (!$targetUser) {
                return response()->json(['status' => 'error', 'message' => 'المستخدم غير موجود!'], 404);
            }

            // حساب عدد الحجوزات الناجحة للمستخدم
            $successfulBookingsCount = DB::table('bookings')
                ->where('user_id', $targetUser->id)
                ->whereIn('status', ['confirmed', 'completed'])
                ->count();

            // شرط أساسي: تفعيل العد فقط إذا أتم المستخدم حجزين ناجحين مسبقاً على الأقل
            if ($successfulBookingsCount < 2) {
                return response()->json([
                    'status' => 'ignored',
                    'message' => 'تم تجاهل تفعيل المخالفة لأن إجمالي الحجوزات الناجحة للمستخدم أقل من حجزين (الحالية: ' . $successfulBookingsCount . ').',
                    'successful_bookings_count' => $successfulBookingsCount,
                    'fake_booking_count' => $targetUser->fake_booking_count,
                    'user_status' => $targetUser->status
                ], 200);
            }

            // في حال وجود حجزين ناجحين مسبقاً، يتم زيادة عدد المخالفات والحظر عند وصولها 5
            $newFakeCount = $targetUser->fake_booking_count + 1;
            $newStatus = ($newFakeCount >= 5) ? 'blocked' : $targetUser->status;

            DB::table('users')
                ->where('id', $targetUser->id)
                ->update([
                    'fake_booking_count' => $newFakeCount,
                    'status' => $newStatus,
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => 'success',
                'message' => ($newStatus === 'blocked') ? 'وصل المستخدم إلى 5 مخالفات وتم حظره تلقائياً!' : 'تم تسجيل المخالفة بنجاح.',
                'fake_booking_count' => $newFakeCount,
                'user_status' => $newStatus,
                'successful_bookings_count' => $successfulBookingsCount
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطأ داخلي: ' . $e->getMessage()], 500);
        }
    }

    /**
     * إلغاء حظر سائق وتصفير مخالفاته
     */
    public function unblockUser(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);

            DB::table('users')
                ->where('id', $request->user_id)
                ->update([
                    'status' => 'active',
                    'fake_booking_count' => 0,
                    'updated_at' => now()
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم فك حظر السائق وتصفير المخالفات بنجاح.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. توليد التقارير الماليّة والتشغيليّة وتصدير PDF/Excel/JSON مع تتبع السيميوليشن وإشغال الخانات
     */
    public function exportReport(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user || $user->role !== 'manager') {
                abort(403, 'غير مصرح لك بالدخول!');
            }

            $manager = DB::table('managers')->where('account_id', $user->id)->first();
            if (!$manager) {
                abort(403, 'لا يوجد ملف تعريف مدير مرتبط بهذا الحساب!');
            }

            $period = $request->input('period');
            $startDateInput = $request->input('start_date') ?? $request->input('start_time');
            $endDateInput = $request->input('end_date') ?? $request->input('end_time');
            $parkingId = $request->input('parking_id');

            $filterPeriodLabel = 'كافة الفترات';
            $startDate = null;
            $endDate = null;

            if ($period === 'today') {
                $startDate = Carbon::today();
                $endDate = Carbon::now();
                $filterPeriodLabel = 'اليوم (' . date('Y-m-d') . ')';
            } elseif ($period === 'this_week') {
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now();
                $filterPeriodLabel = 'هذا الأسبوع';
            } elseif ($period === 'this_month') {
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now();
                $filterPeriodLabel = 'هذا الشهر (' . date('Y-m') . ')';
            } elseif ($startDateInput || $endDateInput) {
                $startDate = $startDateInput ? ((strlen($startDateInput) === 10) ? Carbon::parse($startDateInput)->startOfDay() : Carbon::parse($startDateInput)) : null;
                $endDate = $endDateInput ? ((strlen($endDateInput) === 10) ? Carbon::parse($endDateInput)->endOfDay() : Carbon::parse($endDateInput)) : null;
                $filterPeriodLabel = 'فترة مخصصة';
            }

            // جلب ساحات المدير المحددة
            $managerParkingQuery = DB::table('parkings');
            if ($manager) {
                $managerParkingQuery->where('manager_id', $manager->id);
            }
            if ($parkingId) {
                $managerParkingQuery->where('id', $parkingId);
            }
            $targetParkings = $managerParkingQuery->get();
            $targetParkingIds = $targetParkings->pluck('id')->toArray();

            // استعلام الحجوزات المفلترة حسب الفترة الزمنية
            $bookingsQuery = DB::table('bookings')->whereIn('parking_id', $targetParkingIds);

            if ($startDate) {
                $bookingsQuery->where('start_time', '>=', $startDate);
            }
            if ($endDate) {
                $bookingsQuery->where('start_time', '<=', $endDate);
            }

            $filteredBookings = $bookingsQuery->get();

            // 1. المؤشرات المالية والتشغيلية
            $totalRevenue = (float)$filteredBookings->whereIn('status', ['confirmed', 'completed'])->sum('cost');
            $totalExpenses = 0.00;
            $netProfit = $totalRevenue - $totalExpenses;

            $carsEntered = $filteredBookings->count();
            $carsExited = $filteredBookings->where('status', 'completed')->count();

            $totalParkingHours = 0;
            foreach ($filteredBookings as $b) {
                if ($b->start_time && $b->end_time) {
                    $start = Carbon::parse($b->start_time);
                    $end = Carbon::parse($b->end_time);
                    $totalParkingHours += max(0.5, round($end->diffInMinutes($start) / 60, 1));
                }
            }

            $daysCount = 1;
            if ($startDate && $endDate) {
                $daysCount = max(1, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);
            }
            $avgDailyIncome = round($totalRevenue / $daysCount, 2);

            $totalCapacitySum = $targetParkings->sum('total_capacity') ?: 1;
            $totalOccupiedSpots = $targetParkings->sum(function($p) {
                return max(0, $p->total_capacity - $p->available_capacity);
            });
            $occupancyRate = round(($totalOccupiedSpots / $totalCapacitySum) * 100, 1);

            // 2. سيميوليشن حركة السيارات (Entry & Exit Simulation Data)
            $entryExitSimulation = [];
            $groupedByDate = $filteredBookings->groupBy(function($item) {
                return Carbon::parse($item->start_time)->format('Y-m-d');
            });

            if ($groupedByDate->isEmpty()) {
                $entryExitSimulation[] = [
                    'date' => date('Y-m-d'),
                    'entered_count' => $carsEntered,
                    'exited_count' => $carsExited
                ];
            } else {
                foreach ($groupedByDate as $dateKey => $items) {
                    $entryExitSimulation[] = [
                        'date' => $dateKey,
                        'entered_count' => $items->count(),
                        'exited_count' => $items->where('status', 'completed')->count()
                    ];
                }
            }

            // تتبع إشغال الخانات التفصيلي
            $spotUtilization = [];
            foreach ($targetParkings as $p) {
                for ($spotNum = 1; $spotNum <= $p->total_capacity; $spotNum++) {
                    $spotBookingsCount = $filteredBookings->where('parking_id', $p->id)->count(); 
                    $spotStatus = DB::table('parking_spots')
                        ->where('parking_id', $p->id)
                        ->where('spot_number', $spotNum)
                        ->value('status') ?? 'available';

                    $spotUtilization[] = [
                        'parking_name' => $p->name,
                        'spot_number' => $spotNum,
                        'status' => $spotStatus,
                        'status_label' => ($spotStatus === 'disabled') ? 'معطل' : (($spotStatus === 'occupied') ? 'محجوز' : 'متاح'),
                        'usage_count' => $spotBookingsCount,
                        'utilization_rate' => $carsEntered > 0 ? round(($spotBookingsCount / $carsEntered) * 100, 1) : 0
                    ];
                }
            }

            $exportFormat = strtolower($request->input('format', 'pdf'));

            if ($exportFormat === 'pdf') {
                $pdfData = [
                    'managerName' => $user->name,
                    'filterPeriodLabel' => $filterPeriodLabel,
                    'startDate' => $startDate ? Carbon::parse($startDate)->format('Y-m-d') : null,
                    'endDate' => $endDate ? Carbon::parse($endDate)->format('Y-m-d') : null,
                    'totalRevenue' => $totalRevenue,
                    'totalExpenses' => $totalExpenses,
                    'netProfit' => $netProfit,
                    'carsEntered' => $carsEntered,
                    'carsExited' => $carsExited,
                    'totalParkingHours' => $totalParkingHours,
                    'avgDailyIncome' => $avgDailyIncome,
                    'occupancyRate' => $occupancyRate,
                    'entryExitSimulation' => $entryExitSimulation,
                    'spotUtilization' => $spotUtilization
                ];

                if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.manager_pdf', $pdfData);
                    return $pdf->download('spotly_manager_report_' . date('Y_m_d') . '.pdf');
                } else {
                    $html = view('reports.manager_pdf', $pdfData)->render();
                    return response($html, 200, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="spotly_manager_report.pdf"'
                    ]);
                }
            }

            if ($exportFormat === 'json') {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'filter' => ['period' => $period, 'start_date' => $startDate, 'end_date' => $endDate],
                        'financial' => [
                            'total_revenue' => $totalRevenue,
                            'total_expenses' => $totalExpenses,
                            'net_profit' => $netProfit,
                            'avg_daily_income' => $avgDailyIncome
                        ],
                        'operational' => [
                            'cars_entered' => $carsEntered,
                            'cars_exited' => $carsExited,
                            'total_parking_hours' => $totalParkingHours,
                            'occupancy_rate' => $occupancyRate
                        ],
                        'simulation' => $entryExitSimulation,
                        'spot_utilization' => $spotUtilization
                    ]
                ]);
            }

            // التصدير بصيغة Excel / CSV
            $fileName = 'spotly_financial_report_' . date('Y_m_d');
            $strategy = new \App\Strategies\Export\CsvExportStrategy();
            $exportData = [
                ['تقرير ساحات المدير المالي والتشغيلي الشامل', $user->name],
                ['تاريخ التصدير', date('Y-m-d H:i:s')],
                ['الفترة الزمنية المحددة', $filterPeriodLabel],
                ['', ''],
                ['المؤشر المالي والتشغيلي', 'القيمة'],
                ['إجمالي الإيرادات للفترة (د.ل)', $totalRevenue],
                ['إجمالي المصاريف الخاصة بالفرع (د.ل)', $totalExpenses],
                ['صافي الأرباح (د.ل)', $netProfit],
                ['متوسط الدخل اليومي (د.ل)', $avgDailyIncome],
                ['عدد السيارات الداخلة', $carsEntered],
                ['عدد السيارات الخارجة', $carsExited],
                ['إجمالي ساعات الوقوف', $totalParkingHours],
                ['نسبة إشغال الموقف (%)', $occupancyRate . '%'],
                ['', ''],
                ['التاريخ', 'عدد السيارات الداخلة', 'عدد السيارات الخارجة']
            ];

            foreach ($entryExitSimulation as $sim) {
                $exportData[] = [$sim['date'], $sim['entered_count'], $sim['exited_count']];
            }

            return $strategy->export($exportData, $fileName);

        } catch (\Exception $exception) {
            Log::error('خطأ أثناء تصدير التقرير للمدير: ' . $exception->getMessage());
            abort(500, 'حدث خطأ داخلي أثناء تصدير التقرير: ' . $exception->getMessage());
        }
    }

    /**
     * 4. إدارة وتتبع موظفي الميدان (Staff Management & Tracking CRUD)
     */
    /**
     * 4. إدارة وتتبع طاقم وموظفي الميدان (Multi-Staff Assignment & Shift Roles CRUD)
     */
    public function listEmployees(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $manager = DB::table('managers')->where('account_id', $user->id)->first();
            if (!$manager) {
                return response()->json(['status' => 'error', 'message' => 'سجل المدير غير موجود!'], 403);
            }

            $employees = DB::table('employees')
                ->join('accounts', 'employees.account_id', '=', 'accounts.id')
                ->leftJoin('parkings', 'employees.parking_id', '=', 'parkings.id')
                ->where('parkings.manager_id', $manager->id)
                ->orWhereNull('employees.parking_id')
                ->select(
                    'employees.id as employee_id',
                    'accounts.id as account_id',
                    'accounts.name',
                    'accounts.email',
                    'accounts.phone',
                    'employees.shift_role',
                    'employees.bank_account_number',
                    'parkings.id as parking_id',
                    'parkings.name as parking_name'
                )
                ->get();

            return response()->json(['status' => 'success', 'data' => $employees]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function storeEmployee(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $assignmentType = $request->input('assignment_type', 'create');

            if ($assignmentType === 'select') {
                $request->validate([
                    'employee_id' => 'required|integer|exists:employees,id',
                    'parking_id' => 'required|integer|exists:parkings,id',
                    'shift_role' => 'nullable|string|max:100'
                ]);

                $alreadyAssigned = DB::table('employees')
                    ->where('id', $request->employee_id)
                    ->whereNotNull('parking_id')
                    ->where('parking_id', '!=', $request->parking_id)
                    ->exists();

                if ($alreadyAssigned) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'هذا الموظف معين بالفعل لساحة أخرى!'
                    ], 422);
                }

                $rawRole = $request->input('shift_role', 'الوردية الصباحية');
                $shiftRole = (mb_strpos($rawRole, 'مسائ') !== false || strtolower($rawRole) === 'evening') ? 'الوردية المسائية' : 'الوردية الصباحية';

                DB::table('employees')
                    ->where('id', $request->employee_id)
                    ->update([
                        'parking_id' => $request->parking_id,
                        'shift_role' => $shiftRole,
                        'updated_at' => now()
                    ]);

                // للتوافق الرجعي
                DB::table('parkings')
                    ->where('id', $request->parking_id)
                    ->update([
                        'employee_id' => $request->employee_id,
                        'updated_at' => now()
                    ]);

                $empDetails = DB::table('employees')
                    ->join('accounts', 'employees.account_id', '=', 'accounts.id')
                    ->where('employees.id', $request->employee_id)
                    ->select('employees.id', 'accounts.name', 'accounts.phone', 'employees.shift_role')
                    ->first();

                return response()->json([
                    'status' => 'success',
                    'message' => 'تم تعيين الموظف وتحديد ورديته للساحة بنجاح.',
                    'employee' => [
                        'id' => $empDetails->id,
                        'name' => $empDetails->name,
                        'phone' => $empDetails->phone,
                        'shift_role' => $empDetails->shift_role
                    ]
                ]);
            } else {
                $request->validate([
                    'name' => 'required|string|max:191',
                    'email' => 'required|string|email|max:191|unique:accounts,email',
                    'phone' => 'required|string|max:20',
                    'bank_account_number' => 'nullable|string|max:50',
                    'parking_id' => 'required|integer|exists:parkings,id',
                    'shift_role' => 'nullable|string|max:100'
                ]);

                $generatedPassword = Str::random(8);

                DB::beginTransaction();

                $accountId = DB::table('accounts')->insertGetId([
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'password' => Hash::make($generatedPassword),
                    'role' => 'employee',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $rawRole = $request->input('shift_role', 'الوردية الصباحية');
                $shiftRole = (mb_strpos($rawRole, 'مسائ') !== false || strtolower($rawRole) === 'evening') ? 'الوردية المسائية' : 'الوردية الصباحية';

                $employeeId = DB::table('employees')->insertGetId([
                    'account_id' => $accountId,
                    'parking_id' => $request->parking_id,
                    'shift_role' => $shiftRole,
                    'bank_account_number' => $request->bank_account_number ?? '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('parkings')
                    ->where('id', $request->parking_id)
                    ->update([
                        'employee_id' => $employeeId,
                        'updated_at' => now()
                    ]);

                DB::commit();

                // إرسال البريد الإلكتروني للموظف الجديد مع كلمة المرور المؤقتة
                try {
                    $parkingDetails = DB::table('parkings')->where('id', $request->parking_id)->first();
                    $parkingName = $parkingDetails ? $parkingDetails->name : 'الساحة المعينة';

                    $mailData = [
                        'title' => '🔑 بيانات حساب الموظف الميداني الجديد - SpotLy',
                        'body' => "مرحباً {$request->name}،\n\n" .
                                  "تم إنشاء حساب موظف ميداني جديد لك في نظام SpotLy وتعيينك لإدارة: ({$parkingName}) - الوردية/الدور: ({$shiftRole}).\n\n" .
                                  "اسم المستخدم / البريد الإلكتروني: {$request->email}\n" .
                                  "كلمة المرور المؤقتة: {$generatedPassword}\n\n" .
                                  "يرجى استخدام هذه البيانات لتسجيل الدخول إلى النظام."
                    ];
                    Mail::to($request->email)->send(new SpotlyNotificationMail($mailData));
                } catch (\Exception $mailEx) {
                    Log::error('فشل إرسال بريد إنشاء الموظف: ' . $mailEx->getMessage());
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'تم إنشاء حساب الموظف وتعيينه للساحة ضمن الطاقم بنجاح، وتم إرسال كلمة المرور إلى بريده الإلكتروني.',
                    'employee' => [
                        'id' => $employeeId,
                        'name' => $request->name,
                        'phone' => $request->phone,
                        'shift_role' => $shiftRole
                    ]
                ]);
            }

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

    public function updateEmployee(Request $request, $id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $employee = DB::table('employees')->where('id', $id)->first();
            if (!$employee) {
                return response()->json(['status' => 'error', 'message' => 'الموظف غير موجود!'], 404);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:191',
                'phone' => 'sometimes|required|string|max:20',
                'bank_account_number' => 'nullable|string|max:50',
                'parking_id' => 'nullable|integer|exists:parkings,id'
            ]);

            DB::beginTransaction();

            if ($request->has('name') || $request->has('phone')) {
                $accountUpdate = [];
                if ($request->has('name')) $accountUpdate['name'] = $request->name;
                if ($request->has('phone')) $accountUpdate['phone'] = $request->phone;
                $accountUpdate['updated_at'] = now();

                DB::table('accounts')->where('id', $employee->account_id)->update($accountUpdate);
            }

            if ($request->has('bank_account_number')) {
                DB::table('employees')->where('id', $id)->update([
                    'bank_account_number' => $request->bank_account_number,
                    'updated_at' => now()
                ]);
            }

            if ($request->has('parking_id')) {
                DB::table('parkings')->where('employee_id', $id)->update(['employee_id' => null]);

                if ($request->parking_id) {
                    DB::table('parkings')->where('id', $request->parking_id)->update([
                        'employee_id' => $id,
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'تم تحديث بيانات الموظف بنجاح.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteEmployee($id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $employee = DB::table('employees')->where('id', $id)->first();
            if (!$employee) {
                return response()->json(['status' => 'error', 'message' => 'الموظف غير موجود!'], 404);
            }

            DB::beginTransaction();

            DB::table('parkings')->where('employee_id', $id)->update(['employee_id' => null]);

            $accountId = $employee->account_id;
            DB::table('employees')->where('id', $id)->delete();
            DB::table('accounts')->where('id', $accountId)->delete();

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'تم حذف الموظف بنجاح.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * تتبع نشاط ومناوبات موظف الميدان
     */
    public function getEmployeeTracking($id)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $employee = DB::table('employees')
                ->join('accounts', 'employees.account_id', '=', 'accounts.id')
                ->leftJoin('parkings', 'employees.id', '=', 'parkings.employee_id')
                ->where('employees.id', $id)
                ->select(
                    'employees.id as employee_id',
                    'accounts.name',
                    'accounts.email',
                    'accounts.phone',
                    'employees.bank_account_number',
                    'employees.created_at as registered_at',
                    'parkings.name as current_parking_name'
                )
                ->first();

            if (!$employee) {
                return response()->json(['status' => 'error', 'message' => 'الموظف غير موجود!'], 404);
            }

            $auditLogs = DB::table('activity_cash_audit_logs')
                ->where('employee_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            $totalOperations = $auditLogs->count();
            $totalExitCash = (float)$auditLogs->where('operation_type', 'exit')->sum('cash_value');
            $totalRechargeCash = (float)$auditLogs->where('operation_type', 'recharge')->sum('cash_value');
            $lastShiftTime = $auditLogs->first() ? $auditLogs->first()->created_at : $employee->registered_at;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'employee' => $employee,
                    'shift_info' => [
                        'last_active_shift' => $lastShiftTime,
                        'total_operations' => $totalOperations,
                        'total_exit_cash' => $totalExitCash,
                        'total_recharge_cash' => $totalRechargeCash,
                        'total_cash_handled' => $totalExitCash + $totalRechargeCash
                    ],
                    'recent_activity' => $auditLogs->take(20)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 5. الموقف التفاعلي (Interactive Map API)
     */
    public function getInteractiveMap(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $manager = DB::table('managers')->where('account_id', $user->id)->first();
            if (!$manager) {
                return response()->json(['status' => 'error', 'message' => 'سجل المدير غير موجود!'], 403);
            }

            $parkingId = $request->input('parking_id');
            $parkingQuery = DB::table('parkings')->where('manager_id', $manager->id);
            if ($parkingId) {
                $parkingQuery->where('id', $parkingId);
            }
            $parking = $parkingQuery->first();

            if (!$parking) {
                return response()->json(['status' => 'error', 'message' => 'لا توجد ساحات وقوف تابعة للمدير!'], 404);
            }

            $existingSpots = DB::table('parking_spots')
                ->where('parking_id', $parking->id)
                ->get()
                ->keyBy('spot_number');

            $spots = [];
            for ($num = 1; $num <= $parking->total_capacity; $num++) {
                if (isset($existingSpots[$num])) {
                    $spots[] = [
                        'spot_number' => $num,
                        'status' => $existingSpots[$num]->status,
                        'status_label' => ($existingSpots[$num]->status === 'disabled') ? 'معطل' : (($existingSpots[$num]->status === 'occupied') ? 'محجوز' : 'متاح')
                    ];
                } else {
                    DB::table('parking_spots')->insert([
                        'parking_id' => $parking->id,
                        'spot_number' => $num,
                        'status' => 'available',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $spots[] = [
                        'spot_number' => $num,
                        'status' => 'available',
                        'status_label' => 'متاح'
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'parking' => [
                    'id' => $parking->id,
                    'name' => $parking->name,
                    'total_capacity' => $parking->total_capacity,
                    'available_capacity' => $parking->available_capacity
                ],
                'spots' => $spots
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * تحديث حالة الخانة اللحظية تفاعلياً (متاح، محجوز، معطل)
     */
    public function updateSpotStatus(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $request->validate([
                'parking_id' => 'required|integer|exists:parkings,id',
                'spot_number' => 'required|integer|min:1',
                'status' => 'required|string|in:available,occupied,disabled'
            ]);

            $manager = DB::table('managers')->where('account_id', $user->id)->first();
            $parking = DB::table('parkings')->where('id', $request->parking_id)->where('manager_id', $manager->id)->first();

            if (!$parking) {
                return response()->json(['status' => 'error', 'message' => 'الساحة غير تابعة لهذا المدير!'], 403);
            }

            DB::table('parking_spots')->updateOrInsert(
                ['parking_id' => $request->parking_id, 'spot_number' => $request->spot_number],
                ['status' => $request->status, 'updated_at' => now()]
            );

            $unavailableCount = DB::table('parking_spots')
                ->where('parking_id', $request->parking_id)
                ->whereIn('status', ['occupied', 'disabled'])
                ->count();

            $newAvailableCapacity = max(0, $parking->total_capacity - $unavailableCount);

            DB::table('parkings')->where('id', $request->parking_id)->update([
                'available_capacity' => $newAvailableCapacity,
                'updated_at' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم تحديث حالة الخانة تفاعلياً بنجاح.',
                'spot' => [
                    'spot_number' => $request->spot_number,
                    'status' => $request->status,
                    'status_label' => ($request->status === 'disabled') ? 'معطل' : (($request->status === 'occupied') ? 'محجوز' : 'متاح')
                ],
                'parking_available_capacity' => $newAvailableCapacity
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function unlinkEmployee(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            if ($request->has('employee_id')) {
                DB::table('employees')
                    ->where('id', $request->employee_id)
                    ->update([
                        'parking_id' => null,
                        'updated_at' => now()
                    ]);
            }

            if ($request->has('parking_id')) {
                DB::table('employees')
                    ->where('parking_id', $request->parking_id)
                    ->update([
                        'parking_id' => null,
                        'updated_at' => now()
                    ]);

                DB::table('parkings')
                    ->where('id', $request->parking_id)
                    ->update([
                        'employee_id' => null,
                        'updated_at' => now()
                    ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'تم فك إسناد الموظف عن الطاقم بنجاح.'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم فك ارتباط الموظف عن الساحة بنجاح.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAuditLogsData(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح!'], 403);
            }

            $manager = DB::table('managers')->where('account_id', $user->id)->first();
            if (!$manager) {
                return response()->json(['status' => 'error', 'message' => 'الملف غير موجود!'], 403);
            }

            $managerParkingIds = DB::table('parkings')
                ->where('manager_id', $manager->id)
                ->pluck('id')
                ->toArray();

            $query = DB::table('activity_cash_audit_logs')
                ->join('employees', 'activity_cash_audit_logs.employee_id', '=', 'employees.id')
                ->join('accounts as employee_accounts', 'employees.account_id', '=', 'employee_accounts.id')
                ->join('parkings', 'activity_cash_audit_logs.parking_id', '=', 'parkings.id')
                ->leftJoin('accounts as driver_accounts', 'activity_cash_audit_logs.driver_account_id', '=', 'driver_accounts.id')
                ->whereIn('activity_cash_audit_logs.parking_id', $managerParkingIds)
                ->select(
                    'activity_cash_audit_logs.*',
                    'employee_accounts.name as employee_name',
                    'parkings.name as parking_name',
                    'driver_accounts.name as driver_name'
                )
                ->orderBy('activity_cash_audit_logs.created_at', 'desc');

            if ($request->filled('employee_id')) {
                $query->where('activity_cash_audit_logs.employee_id', $request->employee_id);
            }

            if ($request->filled('operation_type')) {
                $query->where('activity_cash_audit_logs.operation_type', $request->operation_type);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('activity_cash_audit_logs.plate_number', 'like', "%{$search}%")
                      ->orWhere('employee_accounts.name', 'like', "%{$search}%")
                      ->orWhere('driver_accounts.name', 'like', "%{$search}%");
                });
            }

            $logs = $query->get();

            $totalGuestExitCash = 0;
            $totalRechargeCash = 0;
            foreach ($logs as $log) {
                if ($log->operation_type === 'exit') {
                    $totalGuestExitCash += (float)$log->cash_value;
                } elseif ($log->operation_type === 'recharge') {
                    $totalRechargeCash += (float)$log->cash_value;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $logs,
                'summary' => [
                    'totalGuestExitCash' => $totalGuestExitCash,
                    'totalRechargeCash' => $totalRechargeCash,
                    'totalCash' => $totalGuestExitCash + $totalRechargeCash
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function storeParking(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $manager = DB::table('managers')->where('account_id', $user->id)->first();
            if (!$manager) {
                return response()->json(['status' => 'error', 'message' => 'الملف الشخصي غير موجود!'], 403);
            }

            $request->validate([
                'name' => 'required|string|max:191',
                'location_park' => 'required|string|max:191',
                'total_capacity' => 'required|integer|min:1',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            $insertedId = DB::table('parkings')->insertGetId([
                'name' => $request->name,
                'location_park' => $request->location_park,
                'total_capacity' => $request->total_capacity,
                'available_capacity' => $request->total_capacity,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'employee_id' => null,
                'manager_id' => $manager->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم حفظ الموقف بنجاح وربطه بحسابك كمدير.',
                'parking' => [
                    'id' => $insertedId,
                    'name' => $request->name,
                    'total_capacity' => $request->total_capacity,
                    'available_capacity' => $request->total_capacity,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->validator->errors()->first()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ داخلي: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 6. تسجيل بدء المناوبة الميدانية للموظف (Shift Clock-in)
     */
    public function clockInShift(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'employee') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك! خصيص فقط للموظفين الميدانيين.'], 403);
            }

            $employee = DB::table('employees')->where('account_id', $user->id)->first();
            if (!$employee) {
                return response()->json(['status' => 'error', 'message' => 'ملف الموظف غير موجود!'], 404);
            }

            // التحقق مما إذا كان هناك مناوبة نشطة بالفعل
            $activeShift = DB::table('employee_shifts')
                ->where('employee_id', $employee->id)
                ->where('status', 'active')
                ->first();

            if ($activeShift) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لديك مناوبة نشطة بالفعل تم تسجيل بدئها في: ' . Carbon::parse($activeShift->clock_in_at)->format('H:i:s Y-m-d')
                ], 400);
            }

            $shiftId = DB::table('employee_shifts')->insertGetId([
                'employee_id' => $employee->id,
                'clock_in_at' => now(),
                'clock_out_at' => null,
                'status' => 'active',
                'notes' => $request->input('notes'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم تسجيل بدء المناوبة الميدانية بنجاح! نتمنى لك مناوبة موفقة. 🟢',
                'shift' => [
                    'id' => $shiftId,
                    'clock_in_at' => now()->format('Y-m-d H:i:s'),
                    'status' => 'active'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطأ داخلي: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 7. تسجيل إنهاء المناوبة الميدانية للموظف (Shift Clock-out)
     */
    public function clockOutShift(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'employee') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $employee = DB::table('employees')->where('account_id', $user->id)->first();
            if (!$employee) {
                return response()->json(['status' => 'error', 'message' => 'ملف الموظف غير موجود!'], 404);
            }

            $activeShift = DB::table('employee_shifts')
                ->where('employee_id', $employee->id)
                ->where('status', 'active')
                ->first();

            if (!$activeShift) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لا توجد مناوبة نشطة حالياً لتسجيل إنهائها!'
                ], 400);
            }

            $clockIn = Carbon::parse($activeShift->clock_in_at);
            $clockOut = now();
            $durationMinutes = $clockOut->diffInMinutes($clockIn);
            $hours = floor($durationMinutes / 60);
            $minutes = $durationMinutes % 60;

            DB::table('employee_shifts')
                ->where('id', $activeShift->id)
                ->update([
                    'clock_out_at' => $clockOut,
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);

            return response()->json([
                'status' => 'success',
                'message' => "تم تسجيل إنهاء المناوبة بنجاح. إجمالي مدة المناوبة: {$hours} ساعة و {$minutes} دقيقة. 🔴",
                'shift' => [
                    'id' => $activeShift->id,
                    'clock_in_at' => $clockIn->format('Y-m-d H:i:s'),
                    'clock_out_at' => $clockOut->format('Y-m-d H:i:s'),
                    'duration' => "{$hours} ساعة و {$minutes} دقيقة",
                    'status' => 'completed'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطأ داخلي: ' . $e->getMessage()], 500);
        }
    }

    /**
     * 8. جلب حالة مناوبة الموظف الحالية (Shift Status API)
     */
    public function getEmployeeShiftStatus(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'employee') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $employee = DB::table('employees')->where('account_id', $user->id)->first();
            if (!$employee) {
                return response()->json(['status' => 'error', 'message' => 'ملف الموظف غير موجود!'], 404);
            }

            $activeShift = DB::table('employee_shifts')
                ->where('employee_id', $employee->id)
                ->where('status', 'active')
                ->first();

            $lastShift = DB::table('employee_shifts')
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->first();

            return response()->json([
                'status' => 'success',
                'is_active' => (bool)$activeShift,
                'active_shift' => $activeShift ? [
                    'id' => $activeShift->id,
                    'clock_in_at' => Carbon::parse($activeShift->clock_in_at)->format('Y-m-d H:i:s'),
                    'clock_in_formatted' => Carbon::parse($activeShift->clock_in_at)->diffForHumans()
                ] : null,
                'last_shift' => $lastShift ? [
                    'id' => $lastShift->id,
                    'clock_in_at' => Carbon::parse($lastShift->clock_in_at)->format('Y-m-d H:i:s'),
                    'clock_out_at' => $lastShift->clock_out_at ? Carbon::parse($lastShift->clock_out_at)->format('Y-m-d H:i:s') : null,
                    'status' => $lastShift->status
                ] : null
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * 9. عرض وسجل مناوبات الموظفين للمدير (Manager Shift Monitoring Logs)
     */
    public function getShiftLogsData(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح!'], 403);
            }

            $manager = DB::table('managers')->where('account_id', $user->id)->first();
            if (!$manager) {
                return response()->json(['status' => 'error', 'message' => 'الملف غير موجود!'], 403);
            }

            $managerParkingIds = DB::table('parkings')
                ->where('manager_id', $manager->id)
                ->pluck('id')
                ->toArray();

            $query = DB::table('employee_shifts')
                ->join('employees', 'employee_shifts.employee_id', '=', 'employees.id')
                ->join('accounts', 'employees.account_id', '=', 'accounts.id')
                ->leftJoin('parkings', 'employees.id', '=', 'parkings.employee_id')
                ->select(
                    'employee_shifts.*',
                    'accounts.name as employee_name',
                    'accounts.phone as employee_phone',
                    'parkings.name as parking_name'
                )
                ->orderBy('employee_shifts.created_at', 'desc');

            if ($request->filled('employee_id')) {
                $query->where('employee_shifts.employee_id', $request->employee_id);
            }

            if ($request->filled('status')) {
                $query->where('employee_shifts.status', $request->status);
            }

            $shifts = $query->get()->map(function ($shift) {
                $clockIn = Carbon::parse($shift->clock_in_at);
                $clockOut = $shift->clock_out_at ? Carbon::parse($shift->clock_out_at) : null;
                
                $duration = 'مناوبة جارية 🟢';
                if ($clockOut) {
                    $diffInMins = $clockOut->diffInMinutes($clockIn);
                    $h = floor($diffInMins / 60);
                    $m = $diffInMins % 60;
                    $duration = "{$h}س و {$m}د";
                }

                $shift->clock_in_formatted = $clockIn->format('Y-m-d H:i');
                $shift->clock_out_formatted = $clockOut ? $clockOut->format('Y-m-d H:i') : '-';
                $shift->duration_label = $duration;
                return $shift;
            });

            return response()->json([
                'status' => 'success',
                'data' => $shifts
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * توليد التقرير المالي للذكاء الاصطناعي بناءً على بيانات الساحات الخاصة بالمدير
     */
    public function generateAiFinancialReport(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $manager = DB::table('managers')->where('account_id', $user->id)->first();
            $managerId = $manager ? $manager->id : null;

            $scope = $request->query('scope', 'all');
            $timeframe = $request->query('timeframe', 'current_quarter');

            $service = new \App\Services\AiFinancialAnalystService();
            $reportData = $service->generateReport($managerId, $scope, $timeframe);

            return response()->json($reportData);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'خطأ أثناء توليد التقرير الذكي: ' . $e->getMessage()], 500);
        }
    }

    /**
     * معالجة محادثة المساعد الذكي لمدير الساحات
     */
    public function aiChat(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user || $user->role !== 'manager') {
                return response()->json(['status' => 'error', 'message' => 'غير مصرح لك!'], 403);
            }

            $manager = DB::table('managers')->where('account_id', $user->id)->first();
            $managerId = $manager ? $manager->id : null;

            $message = $request->input('message', '');
            $history = $request->input('history', []);

            if (empty(trim($message))) {
                return response()->json(['status' => 'error', 'message' => 'يرجى إدخال السؤال.'], 422);
            }

            $chatService = new \App\Services\AiChatAssistantService();
            $result = $chatService->ask($message, $history, $managerId);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'حدث خطأ في المساعد الذكي: ' . $e->getMessage()], 500);
        }
    }
}
