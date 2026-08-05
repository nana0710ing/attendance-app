<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today()->toDateString())
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

    public function list(Request $request)
    {
        $currentMonth = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->month)
            : Carbon::now();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', auth()->id())
            ->whereYear('work_date', $currentMonth->year)
            ->whereMonth('work_date', $currentMonth->month)
            ->orderBy('work_date')
            ->get();

        return view('attendance.list', compact('attendances', 'currentMonth'));
    }

    public function detail($id)
    {
        $attendance = Attendance::with('breakTimes')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('attendance.detail', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'clock_in' => ['required'],
                'clock_out' => ['required', 'after:clock_in'],
                'break_start.*' => ['nullable'],
                'break_end.*' => ['nullable', 'after:break_start.*'],
                'remark' => ['required'],
            ],
            [
                'clock_in.required' => '出勤時間を入力してください。',
                'clock_out.required' => '退勤時間を入力してください。',
                'clock_out.after' => '退勤時間は出勤時間より後の時間を入力してください。',
                'break_end.*.after' => '休憩終了時間は休憩開始時間より後の時間を入力してください。',
                'remark.required' => '備考を入力してください。',
            ]
        );
        if ($request->clock_in && $request->clock_out) {

            foreach ($request->break_start ?? [] as $index => $breakStart) {

                if (!$breakStart) {
                    continue;
                }

                if (
                    $breakStart < $request->clock_in ||
                    $breakStart > $request->clock_out
                ) {
                    return back()
                        ->withErrors([
                            'break_start' => '休憩時間もしくは退勤時間が不適切な値です'
                        ])
                        ->withInput();
                }
            }

            foreach ($request->break_end ?? [] as $index => $breakEnd) {

                if (!$breakEnd) {
                    continue;
                }

                if ($breakEnd > $request->clock_out) {
                    return back()
                        ->withErrors([
                            'break_end' => '休憩時間もしくは退勤時間が不適切な値です'
                        ])
                        ->withInput();
                }
            }
        }

        $attendance = Attendance::where('user_id', auth()->id())
            ->findOrFail($id);

        $attendance->update([
            'clock_in' => $request->clock_in
                ? Carbon::parse($attendance->work_date . ' ' . $request->clock_in)
                : null,
            'clock_out' => $request->clock_out
                ? Carbon::parse($attendance->work_date . ' ' . $request->clock_out)
                : null,
            'remark' => $request->remark,
        ]);

        foreach ($attendance->breakTimes as $index => $breakTime) {
            $breakTime->update([
                'break_start' => isset($request->break_start[$index])
                    ? Carbon::parse($attendance->work_date . ' ' . $request->break_start[$index])
                    : null,
                'break_end' => isset($request->break_end[$index])
                    ? Carbon::parse($attendance->work_date . ' ' . $request->break_end[$index])
                    : null,
            ]);
        }

        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('message', '勤怠情報を修正しました');
    }

    public function requestList(Request $request)
    {
        $status = $request->status ?? 'pending';

        $attendances = Attendance::where('user_id', auth()->id())
            ->where('approval_status', $status)
            ->get();

        return view('attendance.request_list', compact('status', 'attendances'));
    }
}
