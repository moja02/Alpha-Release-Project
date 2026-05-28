<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\BookingController;

Route::get('/', function () {
    return view('welcome');
});

//  التوجيه التلقائي من الرابط الجذري للنظام إلى صفحة تسجيل الدخول
Route::get('/', function () {
    return redirect('/login');
});

// مسار فتح صفحة تسجيل الدخول العامة (تستدعي ملف resources/views/auth/login.blade.php)
Route::get('/login', function () {
    return view('auth.login');
});

/*
مسارات لوحات التحكم (Dashboards Routes)
*/

// 1. لوحة تحكم المستخدم / السائق (User Dashboard)
Route::get('/user-dashboard', function () {
    return view('dashboards.user');
});

// 2. لوحة تحكم الموظف الميداني (Employee Dashboard)
Route::get('/employee-dashboard', function () {
    return view('dashboards.employee');
});

/* 
مسار احتياطي للأخطاء (Fallback Route)
يعيد توجيه أي شخص يكتب رابطاً غير موجود في المتصفح إلى صفحة الدخول أوتوماتيكياً
*/
Route::fallback(function () {
    return redirect('/login');
});

// مسار فتح واجهة استعادة كلمة المرور عبر الـ OTP
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
});
// مسار دخول وخروج المشتركين
Route::post('/field/user/action', [BookingController::class, 'userFieldAction']);
// مسارات إدارة الميدان
Route::post('/field/guest/entry', [FieldController::class, 'guestEntry']);
Route::post('/field/guest/exit', [FieldController::class, 'guestExit']);
Route::get('/field/parking/capacity', [BookingController::class, 'getParkingCapacity']);
