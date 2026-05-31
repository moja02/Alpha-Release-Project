<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\BookingController;

// 1. مسارات الدخول (لا تكررها)
Route::get('/', fn() => redirect('/login'));
Route::get('/login', fn() => view('auth.login'));
Route::post('/web-login', [AuthController::class, 'login']);

// 2. مسارات لوحات التحكم (Dashboards)
// ملاحظة: تأكد أنك لا تكرر أي مسار هنا
Route::middleware(['web'])->group(function () {
    
    // لوحة المطور
    Route::get('/developer/dashboard', [DeveloperController::class, 'index'])->name('developer.dashboard');
    Route::post('/developer/parkings/store', [DeveloperController::class, 'storeParking'])->name('developer.parking.store');

    // لوحة الموظف
    Route::get('/employee-dashboard', function () {
        return view('dashboards.employee');
    });

    // لوحة المستخدم
    Route::get('/user-dashboard', function () {
        return view('dashboards.user');
    });
});

// 3. مسارات الخدمات (خلفية)
Route::post('/field/user/action', [BookingController::class, 'userFieldAction']);
Route::post('/field/guest/entry', [FieldController::class, 'guestEntry']);
Route::post('/field/guest/exit', [FieldController::class, 'guestExit']);
Route::get('/field/parking/capacity', [BookingController::class, 'getParkingCapacity']);

// 4. مسار احتياطي للأخطاء
Route::fallback(fn() => redirect('/login'));