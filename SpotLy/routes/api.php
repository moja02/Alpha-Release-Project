<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RechargeController;
use App\Http\Controllers\BookingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::post('/accounts/create', [AccountController::class, 'createAccount']);

// مسارات المصادقة (Authentication)
Route::post('/accounts/login', [AuthController::class, 'login']);

// مسار تسجيل الحجز الزائف موجه الآن لمتحكم الحجوزات
Route::post('/accounts/record-fake-booking', [BookingController::class, 'recordFakeBooking']);

// مسارات إدارة الكيانات الأساسية (Account Management)
Route::post('/accounts/create', [AccountController::class, 'createAccount']);
Route::post('/accounts/record-fake-booking', [AccountController::class, 'recordFakeBooking']);

// مسارات الملف الشخصي (Profile Management)
Route::post('/accounts/update-profile', [ProfileController::class, 'updateProfile']);

// مسارات المحفظة والشحن (Wallet & Finances)
Route::get('/recharges/pending', [RechargeController::class, 'getPendingRecharges']);
Route::post('/recharges/verify', [RechargeController::class, 'verifyRequest']);
// إضافة مسار الشحن الفوري الخاص بالموظف
Route::post('/recharges/direct', [RechargeController::class, 'directRecharge']);

// مسارات المحفظة وطلبات التحويل الخاصة بالسائق
Route::get('/wallet/balance', [RechargeController::class, 'getBalance']);
Route::post('/recharges/request', [RechargeController::class, 'submitRequest']);
Route::get('/recharges/user-requests', [RechargeController::class, 'getUserRechargeRequests']);

// مسارات استعادة كلمة المرور عبر الـ OTP
Route::post('/auth/forgot-password/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/forgot-password/reset', [AuthController::class, 'verifyOtpAndResetPassword']);

// مسارات المواقف والحجوزات الخاصة بالسائق (FR3)
Route::get('/parkings/spots', [BookingController::class, 'getSpots']);
Route::get('/bookings/active', [BookingController::class, 'getActiveBooking']);
Route::post('/bookings/create', [BookingController::class, 'createBooking']);