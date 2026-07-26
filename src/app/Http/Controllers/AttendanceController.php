<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('attendance.index', compact('attendance'));
    }

    public function breakStart()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today()->toDateString())
            ->first();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        $attendance->update([
            'status' => '休憩中',
        ]);

        return redirect('/attendance');
    }

    public function breakEnd()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today()->toDateString())
            ->first();

        $breakTime = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->first();

        $breakTime->update([
            'break_end' => now(),
        ]);

        $attendance->update([
            'status' => '出勤中',
        ]);

        return redirect('/attendance');
    }

    public function clockOut()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today()->toDateString())
            ->first();

        $attendance->update([
            'clock_out' => now(),
            'status' => '退勤済',
        ]);

        return redirect('/attendance');
    }

    public function clockIn()
    {
        Attendance::firstOrCreate(
            [
                'user_id' => auth()->id(),
                'work_date' => today()->toDateString(),
            ],
            [
                'clock_in' => now(),
                'status' => '出勤中',
            ]
        );

        return redirect('/attendance');
    }
}
