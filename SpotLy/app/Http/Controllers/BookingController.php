<?php

namespace App\Http\Controllers;

use App\Models\Parking;
use App\Models\Booking;
use App\Models\Wallet;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SpotlyNotificationMail;
use Carbon\Carbon;

class BookingController extends Controller
{
    protected $bookingRepo;

    /**
     * مشيد المتحكم لتهيئة وحقن واجهة مستودع الحجوزات.
     *
     * @param \App\Repositories\BookingRepositoryInterface $bookingRepo
     */
    public function __construct(\App\Repositories\BookingRepositoryInterface $bookingRepo)
    {
        try {
            $this->bookingRepo = $bookingRepo;
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::error("خطأ في تهيئة BookingController: " . $exception->getMessage());
            throw $exception;
        }
    }
     
     // جلب كافة ساحات الوقوف وسعتها المتاحة

    public function getSpots()
    {
        try {
            // استخدام Left Join لضمان ظهور الساحة حتى لو لم يتم تعيين موظف لها بعد
            $parkingAreas = DB::table('parkings')
                ->leftJoin('employees', 'parkings.employee_id', '=', 'employees.id')
                ->select('parkings.*', 'employees.bank_account_number as employee_bank_account')
                ->orderBy('parkings.id')
                ->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $parkingAreas
            ], 200);
        } catch (\Exception $exception) {
            Log::error('Error fetching parking areas: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    public function getRecommendedSpots(Request $request)
    {
        try {
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');
            $sortBy = $request->input('sort_by', 'wsm'); // default to wsm
            $wDist = floatval($request->input('w_dist', 0.5));
            $wAvail = floatval($request->input('w_avail', 0.5));

            $parkingAreas = DB::table('parkings')
                ->leftJoin('employees', 'parkings.employee_id', '=', 'employees.id')
                ->select('parkings.*', 'employees.bank_account_number as employee_bank_account')
                ->get();

            $parkings = $parkingAreas->all();

            if ($lat !== null && $lng !== null) {
                $lat = floatval($lat);
                $lng = floatval($lng);
                foreach ($parkings as $parking) {
                    if (isset($parking->latitude) && isset($parking->longitude)) {
                        $radLat1 = deg2rad($lat);
                        $radLat2 = deg2rad($parking->latitude);
                        $radLng1 = deg2rad($lng);
                        $radLng2 = deg2rad($parking->longitude);
                        $deltaLat = $radLat2 - $radLat1;
                        $deltaLng = $radLng2 - $radLng1;
                        $a = sin($deltaLat/2) * sin($deltaLat/2) + cos($radLat1) * cos($radLat2) * sin($deltaLng/2) * sin($deltaLng/2);
                        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
                        $parking->distance_km = round(6371 * $c, 2);
                    } else {
                        $parking->distance_km = 99999;
                    }
                }
            } else {
                foreach ($parkings as $parking) {
                    $parking->distance_km = null;
                }
            }

            if ($sortBy === 'wsm' && $lat !== null && $lng !== null) {
                $validDistances = array_filter(array_map(fn($p) => $p->distance_km, $parkings), fn($d) => $d !== null && $d < 99999);
                $minDist = count($validDistances) > 0 ? min($validDistances) : 0;
                $maxDist = count($validDistances) > 0 ? max($validDistances) : 1;
                $distRange = $maxDist - $minDist ?: 1;

                foreach ($parkings as $parking) {
                    $normDist = ($parking->distance_km !== null && $parking->distance_km < 99999) 
                        ? (($maxDist - $parking->distance_km) / $distRange) 
                        : 0;

                    $normAvail = $parking->total_capacity > 0 
                        ? ($parking->available_capacity / $parking->total_capacity) 
                        : 0;

                    $score = ($wDist * $normDist) + ($wAvail * $normAvail);
                    $parking->wsm_score = round($score, 4);
                    $parking->match_percentage = round($score * 100);
                }

                usort($parkings, function($a, $b) {
                    return $b->wsm_score <=> $a->wsm_score;
                });
            } else {
                // fallback standard sorting
                usort($parkings, function($a, $b) use ($sortBy) {
                    if ($sortBy === 'available_capacity') {
                        return $b->available_capacity <=> $a->available_capacity;
                    } elseif ($sortBy === 'ratio') {
                        $ratioA = $a->total_capacity > 0 ? ($a->available_capacity / $a->total_capacity) : 0;
                        $ratioB = $b->total_capacity > 0 ? ($b->available_capacity / $b->total_capacity) : 0;
                        return $ratioB <=> $ratioA;
                    } else {
                        if ($a->distance_km === null && $b->distance_km === null) return 0;
                        if ($a->distance_km === null) return 1;
                        if ($b->distance_km === null) return -1;
                        return $a->distance_km <=> $b->distance_km;
                    }
                });
            }

            return response()->json([
                'status' => 'success',
                'sort_by' => $sortBy,
                'w_dist' => $wDist,
                'w_avail' => $wAvail,
                'data' => $parkings
            ], 200);

        } catch (\Exception $exception) {
            Log::error('Error fetching recommended spots: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
    
     // جلب بيانات التذكرة النشطة للسائق (الحجز المبدئي أو الفعلي)
     
    public function getActiveBooking(Request $request)
    {
        try {
            $userId = $request->input('userId');

            // جلب الحجز المؤكد النشط عبر واجهة مستودع الحجوزات (Repository Pattern)
            $activeBooking = $this->bookingRepo->getActiveBookingForUser($userId);

            if ($activeBooking) {
                return response()->json([
                    'status' => 'success',
                    'hasActiveBooking' => true,
                    'bookingData' => $activeBooking
                ], 200);
            } else {
                return response()->json([
                    'status' => 'success',
                    'hasActiveBooking' => false // إذا كان ملغياً أو غير موجود، سيرجع false
                ], 200);
            }

        } catch (\Exception $exception) {
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
    
    /**
     * معالجة واعتماد حجز جديد بناءً على السعة المتاحة
     */
    public function createBooking(Request $request)
    {
        try {
            // 1. التحقق من المدخلات الجديدة بناءً على نوع الحجز
            $request->validate([
                'userId' => 'required|integer',
                'parkingId' => 'required|integer',
                'bookingType' => 'required|in:initial,actual',
                'startTime' => 'required_if:bookingType,actual|date',
                'endTime' => 'required_if:bookingType,actual|date|after:startTime',
            ]);

            $inputUserId = $request->input('userId');
            $inputParkingId = $request->input('parkingId');
            $inputType = $request->input('bookingType');

            \Illuminate\Support\Facades\DB::beginTransaction();

            // 2. منع الحجز المزدوج عبر واجهة المستودع
            $hasExistingBooking = $this->bookingRepo->hasActiveBookingForUser($inputUserId, true);

            if ($hasExistingBooking) {
                return response()->json(['status' => 'error', 'message' => 'لديك حجز نشط بالفعل.'], 400);
            }

            // 3. التحقق من السعة المتاحة (يجب أن تكون أكبر من 0)
            $targetParkingArea = \Illuminate\Support\Facades\DB::table('parkings')
                ->where('id', $inputParkingId)->lockForUpdate()->first();

            if (!$targetParkingArea || $targetParkingArea->available_capacity <= 0) {
                return response()->json(['status' => 'error', 'message' => 'عذراً، هذه الساحة ممتلئة بالكامل.'], 400);
            }

            // جلب رقم اللوحة
            $driverProfile = \Illuminate\Support\Facades\DB::table('users')->where('account_id', $inputUserId)->first();
            $driverPlateNumber = $driverProfile ? $driverProfile->plate_number : 'غير محدد';

            // تهيئة متغير تكلفة الحجز بقيمة صفرية بشكل افتراضي (لنوع الحجز المبدئي)
            $bookingCost = 0.00;

            // 4. معالجة الأوقات والخصم المالي بناءً على السيناريو الخاص بك
            if ($inputType === 'initial') {
                // الحجز المبدئي: يبدأ الآن وينتهي بعد 30 دقيقة (المهلة)
                $startTime = now();
                $endTime = now()->addMinutes(30);
                $notificationMsg = "تم إنشاء حجز مبدئي. أمامك 30 دقيقة للوصول للموقف.";
            } else {
                // الحجز الفعلي: الاعتماد على الأوقات المدخلة من السائق
                $startTime = \Carbon\Carbon::parse($request->input('startTime'));
                $endTime = \Carbon\Carbon::parse($request->input('endTime'));
                
                // حساب عدد الساعات ( تكلفة الساعة 2.5 نقاط)
                $hoursDifference = $startTime->diffInHours($endTime);
                $totalHours = $hoursDifference > 0 ? $hoursDifference : 1; // كحد أدنى ساعة واحدة
                $bookingCost = $totalHours * 2.5;

                // التحقق من الرصيد والخصم
                $userWallet = \Illuminate\Support\Facades\DB::table('wallets')->where('user_id', $inputUserId)->lockForUpdate()->first();
                if (!$userWallet || $userWallet->balance < $bookingCost) {
                    return response()->json(['status' => 'error', 'message' => "رصيدك غير كافٍ. تكلفة الحجز {$bookingCost} نقطة."], 400);
                }
                
                \Illuminate\Support\Facades\DB::table('wallets')->where('user_id', $inputUserId)->decrement('balance', $bookingCost);
                $notificationMsg = "تم تأكيد حجزك الفعلي وخصم {$bookingCost} نقطة من محفظتك.";
            }

            // 5. إنقاص مكان واحد من الساحة المشغولة
            \Illuminate\Support\Facades\DB::table('parkings')->where('id', $inputParkingId)->decrement('available_capacity', 1);

            // 6. إدراج الحجز في قاعدة البيانات عبر واجهة المستودع
            $insertedBookingId = $this->bookingRepo->createBooking([
                'user_id' => $inputUserId,
                'parking_id' => $inputParkingId,
                'plate_number' => $driverPlateNumber,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'type' => $inputType,
                'status' => 'confirmed',
                'cost' => $bookingCost, // حفظ تكلفة الحجز الفعلي في قاعدة البيانات
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // إرسال الإشعار
            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'user_id' => $inputUserId,
                'message' => $notificationMsg,
                'type' => 'Booking_Confirmed',
                'created_at' => now()
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json(['status' => 'success', 'bookingId' => $insertedBookingId], 201);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    /**
     * FR3 - تنفيذ شروط الإلغاء واسترجاع النقاط
     */
    public function cancelBooking(Request $request)
    {
        try {
            $request->validate(['bookingId' => 'required|integer']);
            $targetBookingId = $request->input('bookingId');

            \Illuminate\Support\Facades\DB::beginTransaction();

            // جلب الحجز باستخدام المستودع وتفعيل قفل التحديث (Repository Pattern)
            $bookingRecord = $this->bookingRepo->getById($targetBookingId, true);

            if (!$bookingRecord || $bookingRecord->status !== 'confirmed') {
                return response()->json(['status' => 'error', 'message' => 'الحجز غير موجود أو ملغي مسبقاً.'], 400);
            }

            $currentTime = now();
            $bookingStartTime = \Carbon\Carbon::parse($bookingRecord->start_time);
            $bookingEndTime = \Carbon\Carbon::parse($bookingRecord->end_time);
            
            //   فصل منطق الوقت بناءً على نوع الحجز
            if ($bookingRecord->type === 'actual') {
                // للحجز الفعلي: نمنع الإلغاء إذا حل وقت البداية
                if ($currentTime->greaterThanOrEqualTo($bookingStartTime)) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'عذراً، لا يمكن إلغاء الحجز الفعلي بعد حلول موعد البداية.'
                    ], 403);
                }
            } else {
                // للحجز المبدئي: نمنع الإلغاء إذا انتهت مهلة الـ 20 دقيقة (ستعالج كـ Fake Booking لاحقاً)
                if ($currentTime->greaterThanOrEqualTo($bookingEndTime)) {
                    return response()->json([
                        'status' => 'error', 
                        'message' => 'عذراً، انتهت مهلة الحجز المبدئي (20 دقيقة).'
                    ], 403);
                }
            }

            $refundPercentage = 0;
            $refundAmount = 0;

            // حساب الاسترجاع المالي للحجز الفعلي فقط (لأن المبدئي لم يخصم منه نقاط)
            if ($bookingRecord->type === 'actual') {
                $minutesToStart = (int) $currentTime->diffInMinutes($bookingStartTime, false);
                $totalHours = $bookingStartTime->diffInHours(\Carbon\Carbon::parse($bookingRecord->end_time)) ?: 1;
                $originalCost = (float) ($totalHours * 2.5); // تسعيرة الساعة 2.5

                // استخدام نمط الاستراتيجية (Strategy Pattern)
                $strategy = \App\Strategies\Refund\RefundStrategyFactory::make($bookingRecord);
                $context = new \App\Strategies\Refund\RefundContext($strategy);
                $refundAmount = $context->calculateRefund($originalCost, $minutesToStart);

                $refundPercentage = $originalCost > 0 ? (int) round(($refundAmount / $originalCost) * 100) : 0;

                \Illuminate\Support\Facades\DB::table('wallets')
                    ->where('user_id', $bookingRecord->user_id)
                    ->increment('balance', $refundAmount);
            }

            // تخزين قيمة النقاط المسترجعة كتعويض للمستخدم في قاعدة البيانات
            $bookingRecord->refund_amount = $refundAmount;

            // تحديث حالة الحجز وزيادة السعة المتاحة في الساحة باستخدام نمط الحالة
            $bookingRecord->cancelBooking();

            \Illuminate\Support\Facades\DB::table('parkings')
                ->where('id', $bookingRecord->parking_id)
                ->increment('available_capacity', 1);

            // إرسال إشعار للمستخدم
            $notificationMsg = $bookingRecord->type === 'actual' 
                ? "تم إلغاء الحجز بنجاح. تم استرجاع {$refundAmount} نقطة ({$refundPercentage}%) لمحفظتك."
                : "تم إلغاء الحجز المبدئي بنجاح.";

            \Illuminate\Support\Facades\DB::table('notifications')->insert([
                'user_id' => $bookingRecord->user_id,
                'message' => $notificationMsg,
                'type' => 'Booking_Cancelled',
                'created_at' => now()
            ]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $notificationMsg
            ], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }
    /**
     * تعديل الموقف وتبديله بموقف آخر
     */
    public function changeSpot(Request $request)
    {
        try {
            $request->validate([
                'bookingId' => 'required|integer',
                'newParkingId' => 'required|integer|exists:parkings,id'
            ]);

            $bookingId = $request->input('bookingId');
            $newParkingId = $request->input('newParkingId');

            \Illuminate\Support\Facades\DB::beginTransaction();

            // جلب الحجز باستخدام المستودع وتفعيل قفل التحديث (Repository Pattern)
            $oldBooking = $this->bookingRepo->getById($bookingId, true);
            $currentTime = now();
            
            //  فصل منطق الوقت للتبديل أيضاً
            if ($oldBooking->type === 'actual') {
                if ($currentTime->greaterThanOrEqualTo(\Carbon\Carbon::parse($oldBooking->start_time))) {
                    return response()->json(['status' => 'error', 'message' => 'لا يمكن تغيير الموقف بعد بدء وقت الحجز الفعلي.'], 403);
                }
            } else {
                if ($currentTime->greaterThanOrEqualTo(\Carbon\Carbon::parse($oldBooking->end_time))) {
                    return response()->json(['status' => 'error', 'message' => 'لا يمكن تغيير الموقف لأن مهلة الحجز المبدئي قد انتهت.'], 403);
                }
            }

            // فحص سعة الموقف الجديد
            $newParking = \Illuminate\Support\Facades\DB::table('parkings')->where('id', $newParkingId)->lockForUpdate()->first();
            if ($newParking->available_capacity <= 0) {
                return response()->json(['status' => 'error', 'message' => 'عذراً، الساحة الجديدة ممتلئة بالكامل.'], 400);
            }

            // تنفيذ التبديل وإعادة السعات
            \Illuminate\Support\Facades\DB::table('parkings')->where('id', $oldBooking->parking_id)->increment('available_capacity', 1);
            \Illuminate\Support\Facades\DB::table('parkings')->where('id', $newParkingId)->decrement('available_capacity', 1);

            // تحديث موقف الحجز عبر واجهة المستودع (Repository Pattern)
            $this->bookingRepo->updateBooking($bookingId, ['parking_id' => $newParkingId, 'updated_at' => now()]);

            \Illuminate\Support\Facades\DB::commit();

            return response()->json(['status' => 'success', 'message' => 'تم تبديل الساحة بنجاح.'], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    public function cleanupExpiredBookings()
    {
        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $currentTime = now();

            // 1. جلب كل الحجوزات المبدئية المؤكدة التي انتهت مهلة الـ 20 دقيقة الخاصة بها عبر واجهة المستودع (Repository Pattern)
            $expiredBookings = $this->bookingRepo->getExpiredInitialBookings($currentTime, true);

            $processedCount = 0;

            foreach ($expiredBookings as $booking) {
                // أ. تغيير حالة الحجز إلى 'cancelled' باستخدام نمط الحالة (State Pattern)
                $booking->cancelBooking();

                // ب. إرجاع السعة للساحة
                \Illuminate\Support\Facades\DB::table('parkings')
                    ->where('id', $booking->parking_id)
                    ->increment('available_capacity', 1);

                // ج. إطلاق حدث انتهاء الحجز لتنبيه المراقبين (Observer Pattern)
                event(new \App\Events\BookingExpiredEvent($booking));

                $processedCount++;
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "تمت معالجة {$processedCount} حجوزات منتهية وإرسال الإشعارات."
            ], 200);

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error in cleanup function: ' . $exception->getMessage());
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()], 500);
        }
    }

    // معالجة دخول وخروج المشتركين (أصحاب الحجوزات المسبقة)
    public function userFieldAction(Request $request)
    {
        try {
            $request->validate([
                'plate_number' => 'required|string|max:191',
                'action_type' => 'required|in:entry,exit',
                'user_id' => 'required',
                'expected_exit_time' => 'nullable|date_format:H:i'
            ]);

            $plateNumber = $request->input('plate_number');
            $actionType = $request->input('action_type');
            $userId = $request->input('user_id');
            $expectedTimeStr = $request->input('expected_exit_time');

            $employee = DB::table('employees')->where('account_id', $userId)->first();
            if (!$employee) return response()->json(['status' => 'error', 'message' => 'هذا الحساب ليس موظفاً ميدانياً.'], 403);

            $parking = DB::table('parkings')->where('employee_id', $employee->id)->first();
            if (!$parking) return response()->json(['status' => 'error', 'message' => 'لا توجد ساحة معينة لك.'], 404);
            $employeeParkingId = $parking->id;

            DB::beginTransaction();

            if ($actionType === 'entry') {
                $parkingData = DB::table('parkings')->where('id', $employeeParkingId)->lockForUpdate()->first();
                if ($parkingData->available_capacity <= 0) return response()->json(['status' => 'error', 'message' => 'الموقف ممتلئ!'], 400);

                // التحقق من وجود السيارة بالداخل عبر واجهة المستودع (Repository Pattern)
                $alreadyInside = $this->bookingRepo->getActiveBookingByPlate($plateNumber);
                if ($alreadyInside) return response()->json(['status' => 'error', 'message' => 'السيارة موجودة بالفعل.'], 400);

                // جلب الحجز المؤكد عبر واجهة المستودع (Repository Pattern)
                $booking = $this->bookingRepo->getConfirmedBookingByPlate($plateNumber);
                if (!$booking) return response()->json(['status' => 'error', 'message' => 'لا يوجد حجز مسبق مؤكد لهذه اللوحة.'], 404);

                if ($booking->type === 'initial') {
                    // إذا كان الحجز مبدئياً ولم يقم الموظف بإدخال الوقت بعد
                    if (!$expectedTimeStr) {
                        DB::rollBack();
                        return response()->json([
                            'status' => 'requires_time', 
                            'message' => 'هذا الحجز مبدئي. يرجى إدخال وقت الخروج المتوقع لخصم الرصيد.'
                        ]);
                    }

                    // إذا أرسل الموظف الوقت، نتحقق من الرصيد الأساسي أولاً
                    $expectedEndTime = Carbon::createFromFormat('H:i', $expectedTimeStr);
                    if ($expectedEndTime->isPast()) $expectedEndTime->addDay();
                    
                    $durationMinutes = Carbon::now()->diffInMinutes($expectedEndTime);
                    $durationHours = ceil($durationMinutes / 60) == 0 ? 1 : ceil($durationMinutes / 60);
                    $expectedCost = $durationHours * 2.5; // التسعيرة الأساسية

                    $wallet = DB::table('wallets')->where('user_id', $booking->user_id)->first();
                    if (!$wallet || $wallet->balance < $expectedCost) {
                        return response()->json(['status' => 'error', 'message' => 'رصيد غير كافٍ! (المطلوب: ' . $expectedCost . ')'], 400);
                    }

                    // الدخول (حفظ وقت الخروج المتوقع في end_time لمقارنته عند الخروج الفعلي) باستخدام نمط الحالة
                    $booking->end_time = $expectedEndTime;
                    $booking->enter();
                    $message = 'تم تأكيد الدخول المبدئي بنجاح. سيتم احتساب التكلفة الفعالية والعقوبات عند الخروج.';

                } else {
                    // إذا كان الحجز (actual) فعلي ومسبق الدفع، يتم الدخول باستخدام نمط الحالة
                    $booking->enter();
                    $message = 'تم تأكيد الدخول الفعلي بنجاح فوراً.';
                }

                DB::table('parkings')->where('id', $employeeParkingId)->decrement('available_capacity', 1);

            } else {
                // --- منطق الخروج (تطبيق العقوبات والخصم) ---
                // جلب الحجز النشط عبر واجهة المستودع (Repository Pattern)
                $booking = $this->bookingRepo->getActiveBookingInParking($plateNumber, $employeeParkingId);

                if (!$booking) return response()->json(['status' => 'error', 'message' => 'السيارة غير موجودة بالموقف.'], 404);

                $exitTime = Carbon::now();
                $expectedExitTime = Carbon::parse($booking->end_time); // الوقت المتوقع المخزن عند الدخول أو الحجز المسبق
                
                $penaltyPoints = 0;
                $hasDelay = false;

                // قاعدة العقوبة المشتركة للمبدئي والفعلي
                // إذا تجاوز الوقت الحالي وقت الخروج المتوقع
                if ($exitTime->gt($expectedExitTime)) {
                    $delayMinutes = $exitTime->diffInMinutes($expectedExitTime);
                    $penaltyPoints = ceil($delayMinutes / 30); // نقطة واحدة لكل 30 دقيقة أو كسرها
                    $hasDelay = true;
                }

                if ($booking->type === 'initial') {
                    // --- حساب التكلفة للحجز المبدئي ---
                    $entryTime = Carbon::parse($booking->start_time);
                    $actualMinutes = $entryTime->diffInMinutes($exitTime);
                    $actualHours = ceil($actualMinutes / 60) == 0 ? 1 : ceil($actualMinutes / 60);
                    
                    // التكلفة الإجمالية = الساعات الفعلية * 2.5 + نقاط العقوبة
                    $finalCost = ($actualHours * 2.5) + $penaltyPoints;

                    DB::table('wallets')->where('user_id', $booking->user_id)->decrement('balance', $finalCost);
                    
                    $message = 'تم تسجيل الخروج وخصم ' . $finalCost . ' من المحفظة.';
                    if ($hasDelay) {
                        $message .= ' (شاملة عقوبة تأخير: ' . $penaltyPoints . ' نقطة).';
                    }
                } else {
                    // --- حساب التكلفة للحجز الفعلي (مسبق الدفع) ---
                    // الحجز الفعلي مدفوع قيمته مسبقاً، لذا نخصم فقط قيمة العقوبة إن وجدت
                    if ($hasDelay) {
                        DB::table('wallets')->where('user_id', $booking->user_id)->decrement('balance', $penaltyPoints);
                        $message = 'تم تسجيل خروج المشترك. تم خصم عقوبة تأخير بقيمة ' . $penaltyPoints . ' نقطة من المحفظة لتجاوز الوقت المحدد.';
                    } else {
                        $message = 'تم خروج المشترك بنجاح. (مدفوع مسبقاً وبدون تأخير).';
                    }
                }

                // إرسال إشعار البريد الإلكتروني في حالة وجود تأخير
                if ($hasDelay) {
                    $user = DB::table('users')->where('id', $booking->user_id)->first();
                    if ($user && !empty($user->email)) {
                        try {
                            \Illuminate\Support\Facades\Mail::to($user->email)->send(
                                new \App\Mail\LateExitNotification($booking, $penaltyPoints)
                            );
                        } catch (\Exception $mailException) {
                            // نلتقط خطأ الإيميل حتى لا يتعطل كود الخروج في حال عدم إعداد الـ SMTP بشكل صحيح
                            \Log::error('فشل إرسال بريد التأخير للمشترك: ' . $mailException->getMessage());
                        }
                    }
                }

                // تحديث حالة الحجز إلى مكتمل باستخدام نمط الحالة
                $booking->exitParking();

                DB::table('parkings')->where('id', $employeeParkingId)->increment('available_capacity', 1);
            }

            // تسجيل العملية في سجل التدقيق المالي الميداني
            DB::table('activity_cash_audit_logs')->insert([
                'employee_id' => $employee->id,
                'parking_id' => $employeeParkingId,
                'operation_type' => $actionType, // 'entry' or 'exit'
                'plate_number' => $plateNumber,
                'cash_value' => 0.00, // السائق المشترك يدفع بالنقاط الرقمية، لا يوجد كاش مستلم عند البوابة
                'driver_account_id' => $booking->user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'خطأ: ' . $e->getMessage()], 500);
        }
    }

    //حساب الاماكن الشاغرة المتبقية في الموقف 
    public function getParkingCapacity(Request $request)
    {
        // نستقبل معرف الموظف الذي أرسلناه من المتصفح
        $userId = $request->input('user_id');

        $employee = DB::table('employees')->where('account_id', $userId)->first();
        if (!$employee) return response()->json(['capacity' => 0]);

        $parking = DB::table('parkings')->where('employee_id', $employee->id)->first();
        if (!$parking) return response()->json(['capacity' => 0]);

        return response()->json(['capacity' => $parking->available_capacity]);
    }

}

