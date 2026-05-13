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
Route::post('/recharges/verify', [RechargeController::class, 'verifyRechargeRequest']);