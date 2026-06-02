<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\BookingController;

// 1. مسارات الدخول 
Route::get('/', fn() => redirect('/login'));
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/web-login', [AuthController::class, 'login']);

// 2. مسارات لوحات التحكم (Dashboards)
Route::middleware(['web', 'auth'])->group(function () {
    
    // حماية لوحة المطور
    Route::get('/developer/dashboard', [DeveloperController::class, 'index'])
        ->middleware('role:developer')
        ->name('developer.dashboard');

    // حماية لوحة الموظف
    Route::get('/employee-dashboard', function () {
        return view('dashboards.employee');
    })->middleware('role:employee');

    // حماية لوحة المستخدم
    Route::get('/user-dashboard', function () {
        return view('dashboards.user');
    })->middleware('role:user');
});

Route::post('/developer/parkings/store', [DeveloperController::class, 'storeParking'])->name('developer.parking.store');
    

// 3. مسارات الخدمات (خلفية)
Route::post('/field/user/action', [BookingController::class, 'userFieldAction']);
Route::post('/field/guest/entry', [FieldController::class, 'guestEntry']);
Route::post('/field/guest/exit', [FieldController::class, 'guestExit']);
Route::get('/field/parking/capacity', [BookingController::class, 'getParkingCapacity']);

// 4. مسار احتياطي للأخطاء
Route::fallback(fn() => redirect('/login'));