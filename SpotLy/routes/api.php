<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// مسار إنشاء الحساب
Route::post('/accounts/create', [AccountController::class, 'createAccount']);

// مسار تسجيل الحجز الزائف وفحص الحظر التلقائي
Route::post('/accounts/record-fake-booking', [AccountController::class, 'recordFakeBooking']);

// مسار تسجيل الدخول للتحقق من الهوية وجلب الصلاحيات
Route::post('/accounts/login', [AccountController::class, 'login']);