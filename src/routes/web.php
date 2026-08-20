<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware(['auth', 'verified']);

Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.clock-in');

Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.clock-out');

Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.break-start');

Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.break-end');

Route::get('/attendance/list', [AttendanceController::class, 'list'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.list');

Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.detail');

Route::patch('/attendance/detail/{id}', [AttendanceController::class, 'update'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.update');

Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])
    ->middleware(['auth', 'verified'])
    ->name('request.list');

Route::get('/attendance/report', [AttendanceController::class, 'report'])
    ->middleware(['auth', 'verified'])
    ->name('attendance.report');

Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');

Route::post('/admin/login', [AdminLoginController::class, 'login'])
    ->name('admin.login.submit');

Route::post('/admin/logout', [AdminLoginController::class, 'logout'])
    ->name('admin.logout');

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/requests', [AttendanceController::class, 'adminRequestList'])
        ->name('admin.requests');

    Route::get('/admin/requests/{id}', [AttendanceController::class, 'adminRequestDetail'])
    ->name('admin.requests.detail');

    Route::patch('/stamp_correction_request/approve/{attendance_correct_request_id}',
    [AttendanceController::class, 'approveRequest'])
    ->name('admin.requests.approve');

    Route::get('/admin/staff/list', [AttendanceController::class, 'staffList'])
    ->name('admin.staff.list');

    Route::get('/admin/attendance/staff/{id}', [AttendanceController::class, 'staffAttendance'])
    ->name('admin.staff.attendance');

     Route::get('/admin/attendance/list', [AttendanceController::class, 'adminAttendanceList'])
    ->name('admin.attendance.list');

    Route::get('/admin/attendance/{id}', [AttendanceController::class, 'adminAttendanceDetail'])
    ->name('admin.attendance.detail');

    Route::patch('/admin/attendance/{id}', [AttendanceController::class, 'adminAttendanceUpdate'])
    ->name('admin.attendance.update');

    Route::get('/admin/staff/{user}/csv', [AttendanceController::class, 'exportCsv'])
    ->name('admin.staff.csv');

});