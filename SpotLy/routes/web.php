<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ManagerController;

// 1. مسارات الدخول 
Route::get('/', fn() => redirect('/login'));
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/web-login', [AuthController::class, 'login']);

// 2. مسارات لوحات التحكم (Dashboards)
Route::middleware(['web', 'auth'])->group(function () {
    
    // حماية لوحة المطور والعمليات الخاصة بها (متاحة للمطور والمدير)
    Route::middleware('role:developer,manager')->group(function () {
        Route::get('/developer/dashboard', [DeveloperController::class, 'index'])->name('developer.dashboard');
        Route::post('/developer/parkings/store', [DeveloperController::class, 'storeParking'])->name('developer.parking.store');
        Route::post('/developer/managers/store', [DeveloperController::class, 'storeManager'])->name('developer.manager.store');
        Route::post('/developer/managers/{id}/toggle-status', [DeveloperController::class, 'toggleManagerStatus'])->name('developer.manager.toggle-status');
        Route::get('/developer/managers/{id}/parkings', [DeveloperController::class, 'getManagerParkings'])->name('developer.manager.parkings');
        Route::post('/developer/managers/{id}/parkings', [DeveloperController::class, 'updateManagerParkings'])->name('developer.manager.parkings.update');
    });

    // حماية لوحة الموظف
    Route::get('/employee-dashboard', function () {
        return view('dashboards.employee');
    })->middleware('role:employee');

    // حماية لوحة المستخدم
    Route::get('/user-dashboard', function () {
        return view('dashboards.user');
    })->middleware('role:user');

    // حماية لوحة المدير
    Route::get('/manager/dashboard', [ManagerController::class, 'index'])
        ->middleware('role:manager')
        ->name('manager.dashboard');
    Route::post('/manager/parkings/store', [ManagerController::class, 'storeParking'])
        ->middleware('role:manager')
        ->name('manager.parking.store');
    Route::post('/manager/employees/store', [ManagerController::class, 'storeEmployee'])
        ->middleware('role:manager')
        ->name('manager.employee.store');
    Route::post('/manager/parkings/unlink', [ManagerController::class, 'unlinkEmployee'])
        ->middleware('role:manager')
        ->name('manager.parking.unlink');
    Route::post('/manager/users/unblock', [ManagerController::class, 'unblockUser'])
        ->middleware('role:manager')
        ->name('manager.users.unblock');
    Route::get('/manager/reports/export', [ManagerController::class, 'exportReport'])
        ->middleware('role:manager')
        ->name('manager.reports.export');
    Route::get('/manager/audit-logs/data', [ManagerController::class, 'getAuditLogsData'])
        ->middleware('role:manager')
        ->name('manager.audit-logs.data');
});
    

// 3. مسارات الخدمات (خلفية)
Route::post('/field/user/action', [BookingController::class, 'userFieldAction']);
Route::post('/field/guest/entry', [FieldController::class, 'guestEntry']);
Route::post('/field/guest/exit', [FieldController::class, 'guestExit']);
Route::get('/field/parking/capacity', [BookingController::class, 'getParkingCapacity']);

// 4. مسار احتياطي للأخطاء
Route::fallback(fn() => redirect('/login'));