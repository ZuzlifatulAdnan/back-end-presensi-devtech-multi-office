<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    $user = $request->user();

    // Load relationships
    $user->load(['shiftKerja', 'departemen']);

    return response([
        'user' => $user,
        'role' => $user->role,
        'position' => $user->position,
        'default_shift' => $user->shiftKerja ? [
            'id' => $user->shiftKerja->id,
            'name' => $user->shiftKerja->name,
        ] : null,
        'default_shift_detail' => $user->shiftKerja ? [
            'id' => $user->shiftKerja->id,
            'name' => $user->shiftKerja->name,
            'start_time' => $user->shiftKerja->start_time,
            'end_time' => $user->shiftKerja->end_time,
        ] : null,
        'department' => $user->departemen ? [
            'id' => $user->departemen->id,
            'name' => $user->departemen->name,
        ] : null,
    ], 200);
})->middleware('auth:sanctum');

// login
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

// logout
Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');

// me
Route::get('/me', [App\Http\Controllers\Api\AuthController::class, 'me'])->middleware('auth:sanctum');

// company
Route::get('/company', [App\Http\Controllers\Api\CompanyController::class, 'show'])->middleware('auth:sanctum');

// checkin
Route::post('/checkin', [App\Http\Controllers\Api\AttendanceController::class, 'checkin'])->middleware('auth:sanctum');

// checkout
Route::post('/checkout', [App\Http\Controllers\Api\AttendanceController::class, 'checkout'])->middleware('auth:sanctum');

// is checkin
Route::get('/is-checkin', [App\Http\Controllers\Api\AttendanceController::class, 'isCheckedin'])->middleware('auth:sanctum');

// update profile
Route::post('/update-profile', [App\Http\Controllers\Api\AuthController::class, 'updateProfile'])->middleware('auth:sanctum');

// notes
Route::apiResource('/api-notes', App\Http\Controllers\Api\NoteController::class)->middleware('auth:sanctum');

// update fcm token
Route::post('/update-fcm-token', [App\Http\Controllers\Api\AuthController::class, 'updateFcmToken'])->middleware('auth:sanctum');

// get attendance
Route::get('/api-attendances', [App\Http\Controllers\Api\AttendanceController::class, 'index'])->middleware('auth:sanctum');

Route::get('/api-user/{id}', [App\Http\Controllers\Api\UserController::class, 'getUserId'])->middleware('auth:sanctum');

// update user
Route::post('/api-user/edit', [App\Http\Controllers\Api\UserController::class, 'updateProfile'])->middleware('auth:sanctum');

// overtime
Route::post('/start-overtime', [App\Http\Controllers\Api\OvertimeController::class, 'startOvertime'])->middleware('auth:sanctum');
Route::post('/end-overtime', [App\Http\Controllers\Api\OvertimeController::class, 'endOvertime'])->middleware('auth:sanctum');
Route::get('/overtime-status', [App\Http\Controllers\Api\OvertimeController::class, 'checkTodayOvertimeStatus'])->middleware('auth:sanctum');
Route::get('/overtimes', [App\Http\Controllers\Api\OvertimeController::class, 'index'])->middleware('auth:sanctum');

// leave
Route::get('/leave-types', [App\Http\Controllers\Api\LeaveController::class, 'getLeaveTypes'])->middleware('auth:sanctum');
Route::get('/leave-balance', [App\Http\Controllers\Api\LeaveController::class, 'getBalance'])->middleware('auth:sanctum');
Route::get('/leaves', [App\Http\Controllers\Api\LeaveController::class, 'index'])->middleware('auth:sanctum');
Route::get('/leaves/{id}', [App\Http\Controllers\Api\LeaveController::class, 'show'])->middleware('auth:sanctum');
Route::post('/leaves', [App\Http\Controllers\Api\LeaveController::class, 'store'])->middleware('auth:sanctum');
Route::put('/leaves/{id}', [App\Http\Controllers\Api\LeaveController::class, 'update'])->middleware('auth:sanctum');
Route::post('/leaves/{id}/cancel', [App\Http\Controllers\Api\LeaveController::class, 'cancel'])->middleware('auth:sanctum');
