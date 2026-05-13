<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// تعليق مضمن: التوجيه التلقائي من الرابط الجذري للنظام إلى صفحة تسجيل الدخول
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