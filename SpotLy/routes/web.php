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
Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
Route::post('/web-login', [AuthController::class, 'login']);

// 2. مسارات لوحات التحكم (Dashboards)
Route::middleware(['web', 'auth'])->group(function () {
    
    // حماية لوحة المطور والعمليات الخاصة بها (متاحة للمطور والمدير)
    Route::middleware('role:developer,manager')->group(function () {
        Route::get('/developer/dashboard', [DeveloperController::class, 'index'])->name('developer.dashboard');
        Route::post('/developer/parkings/store', [DeveloperController::class, 'storeParking'])->name('developer.parking.store');
        Route::delete('/developer/parkings/{id}', [DeveloperController::class, 'deleteParking'])->name('developer.parking.delete');
        Route::post('/developer/parkings/{id}/delete', [DeveloperController::class, 'deleteParking']);
        Route::post('/developer/managers/store', [DeveloperController::class, 'storeManager'])->name('developer.manager.store');
        Route::post('/developer/managers/{id}/toggle-status', [DeveloperController::class, 'toggleManagerStatus'])->name('developer.manager.toggle-status');
        Route::get('/developer/managers/{id}/parkings', [DeveloperController::class, 'getManagerParkings'])->name('developer.manager.parkings');
        Route::post('/developer/managers/{id}/parkings', [DeveloperController::class, 'updateManagerParkings'])->name('developer.manager.parkings.update');
        Route::delete('/developer/managers/{id}', [DeveloperController::class, 'deleteManager'])->name('developer.manager.delete');
        Route::post('/developer/managers/{id}/delete', [DeveloperController::class, 'deleteManager']);
        Route::get('/developer/ai-financial-report', [DeveloperController::class, 'generateAiFinancialReport'])->name('developer.ai-report');
        Route::post('/developer/ai-chat', [DeveloperController::class, 'aiChat'])->name('developer.ai-chat');
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
    Route::post('/manager/users/violation', [ManagerController::class, 'handleViolation'])
        ->middleware('role:manager')
        ->name('manager.users.violation');
    Route::get('/manager/employees', [ManagerController::class, 'listEmployees'])
        ->middleware('role:manager')
        ->name('manager.employees.list');
    Route::put('/manager/employees/{id}', [ManagerController::class, 'updateEmployee'])
        ->middleware('role:manager')
        ->name('manager.employees.update');
    Route::delete('/manager/employees/{id}', [ManagerController::class, 'deleteEmployee'])
        ->middleware('role:manager')
        ->name('manager.employees.delete');
    Route::get('/manager/employees/{id}/tracking', [ManagerController::class, 'getEmployeeTracking'])
        ->middleware('role:manager')
        ->name('manager.employees.tracking');
    Route::get('/manager/interactive-map', [ManagerController::class, 'getInteractiveMap'])
        ->middleware('role:manager')
        ->name('manager.interactive-map');
    Route::post('/manager/spots/update-status', [ManagerController::class, 'updateSpotStatus'])
        ->middleware('role:manager')
        ->name('manager.spots.update-status');
    Route::get('/manager/reports/export', [ManagerController::class, 'exportReport'])
        ->middleware('role:manager')
        ->name('manager.reports.export');
    Route::get('/manager/audit-logs/data', [ManagerController::class, 'getAuditLogsData'])
        ->middleware('role:manager')
        ->name('manager.audit-logs.data');
    Route::get('/manager/shifts/data', [ManagerController::class, 'getShiftLogsData'])
        ->middleware('role:manager')
        ->name('manager.shifts.data');
    Route::get('/manager/ai-financial-report', [ManagerController::class, 'generateAiFinancialReport'])
        ->middleware('role:manager')
        ->name('manager.ai-report');
    Route::post('/manager/ai-chat', [ManagerController::class, 'aiChat'])
        ->middleware('role:manager')
        ->name('manager.ai-chat');

    // Shift Clock-in / Clock-out routes for field employee
    Route::get('/employee/shift/status', [ManagerController::class, 'getEmployeeShiftStatus'])
        ->middleware('role:employee');
    Route::post('/employee/shift/clock-in', [ManagerController::class, 'clockInShift'])
        ->middleware('role:employee');
    Route::post('/employee/shift/clock-out', [ManagerController::class, 'clockOutShift'])
        ->middleware('role:employee');
});
    

// 3. مسارات الخدمات (خلفية)
Route::post('/field/user/action', [BookingController::class, 'userFieldAction']);
Route::post('/field/guest/entry', [FieldController::class, 'guestEntry']);
Route::post('/field/guest/exit', [FieldController::class, 'guestExit']);
Route::get('/field/parking/capacity', [BookingController::class, 'getParkingCapacity']);

// 4. مسار احتياطي للأخطاء
Route::fallback(fn() => redirect('/login'));