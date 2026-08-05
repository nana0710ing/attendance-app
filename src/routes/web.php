<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

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
    return view('welcome');
});

Route::get('/attendance', [AttendanceController::class, 'index'])
    ->middleware('auth');

Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
    ->middleware('auth')
    ->name('attendance.clock-in');

Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->middleware('auth')
    ->name('attendance.clock-out');

Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
    ->middleware('auth')
    ->name('attendance.break-start');

Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
    ->middleware('auth')
    ->name('attendance.break-end');

Route::get('/attendance/list', [AttendanceController::class, 'list'])
    ->middleware('auth')
    ->name('attendance.list');

Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
    ->middleware('auth')
    ->name('attendance.detail');

Route::patch('/attendance/detail/{id}', [AttendanceController::class, 'update'])
    ->middleware('auth')
    ->name('attendance.update');