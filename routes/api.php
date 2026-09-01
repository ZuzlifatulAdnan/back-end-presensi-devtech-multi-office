<?php

use App\Http\Controllers\Api\AppSettingController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\OvertimeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public endpoints
|--------------------------------------------------------------------------
| Branding is public so the splash and login screens can render the correct
| app name, logo and theme before a token exists.
*/

Route::get('/app-settings', [AppSettingController::class, 'show'])->name('api.app-settings');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:20,1')
    ->name('api.login');

/*
|--------------------------------------------------------------------------
| Authenticated endpoints
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Session
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('api.logout-all');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');
    Route::get('/user', [AuthController::class, 'me'])->name('api.user');

    // Profile
    Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('api.face.update');
    Route::post('/update-fcm-token', [AuthController::class, 'updateFcmToken'])->name('api.fcm.update');
    Route::get('/api-user/{id}', [UserController::class, 'getUserId'])
        ->whereNumber('id')
        ->name('api.user.show');
    Route::post('/api-user/edit', [UserController::class, 'updateProfile'])->name('api.user.update');

    // Password
    Route::post('/api-user/update-password', [UserController::class, 'updatePassword'])
        ->middleware('throttle:10,1')
        ->name('api.password.update');
    Route::post('/change-password', [UserController::class, 'updatePassword'])
        ->middleware('throttle:10,1')
        ->name('api.password.change');

    // Offices / map data
    Route::get('/company', [CompanyController::class, 'show'])->name('api.company');
    Route::get('/companies', [CompanyController::class, 'index'])->name('api.companies');

    // Attendance
    Route::prefix('attendance')->name('api.attendance.')->group(function () {
        Route::get('/pre-check', [AttendanceController::class, 'preCheck'])->name('pre-check');
        Route::get('/locations', [AttendanceController::class, 'locations'])->name('locations');
        Route::get('/today', [AttendanceController::class, 'today'])->name('today');
        Route::get('/summary', [AttendanceController::class, 'summary'])->name('summary');
        Route::get('/history', [AttendanceController::class, 'index'])->name('history');
        Route::post('/check-in', [AttendanceController::class, 'checkin'])
            ->middleware('throttle:30,1')
            ->name('check-in');
        Route::post('/check-out', [AttendanceController::class, 'checkout'])
            ->middleware('throttle:30,1')
            ->name('check-out');
    });

    // Attendance aliases used by existing app builds
    Route::post('/checkin', [AttendanceController::class, 'checkin'])->middleware('throttle:30,1');
    Route::post('/checkout', [AttendanceController::class, 'checkout'])->middleware('throttle:30,1');
    Route::get('/is-checkin', [AttendanceController::class, 'isCheckedin']);
    Route::get('/api-attendances', [AttendanceController::class, 'index']);

    // Overtime
    Route::post('/start-overtime', [OvertimeController::class, 'startOvertime'])->name('api.overtime.start');
    Route::post('/end-overtime', [OvertimeController::class, 'endOvertime'])->name('api.overtime.end');
    Route::get('/overtime-status', [OvertimeController::class, 'checkTodayOvertimeStatus'])->name('api.overtime.status');
    Route::get('/overtimes', [OvertimeController::class, 'index'])->name('api.overtime.index');

    // Izin & cuti
    Route::get('/leave-types', [LeaveController::class, 'getLeaveTypes'])->name('api.leave.types');
    Route::get('/leave-balance', [LeaveController::class, 'getBalance'])->name('api.leave.balance');
    Route::get('/leaves', [LeaveController::class, 'index'])->name('api.leave.index');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('api.leave.store');
    Route::get('/leaves/{id}', [LeaveController::class, 'show'])->whereNumber('id')->name('api.leave.show');
    Route::match(['put', 'post'], '/leaves/{id}', [LeaveController::class, 'update'])
        ->whereNumber('id')
        ->name('api.leave.update');
    Route::post('/leaves/{id}/cancel', [LeaveController::class, 'cancel'])->whereNumber('id')->name('api.leave.cancel');
    Route::post('/leaves/{id}/approve', [LeaveController::class, 'approve'])->whereNumber('id')->name('api.leave.approve');
    Route::post('/leaves/{id}/reject', [LeaveController::class, 'reject'])->whereNumber('id')->name('api.leave.reject');

    // Notes
    Route::apiResource('/api-notes', NoteController::class);
});
