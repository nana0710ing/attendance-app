<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use App\Models\StampCorrectionRequest;
use App\Models\User;

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

        $pendingRequest = StampCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('approval_status', 'pending')
            ->exists();

        return view('attendance.detail', compact('attendance', 'pendingRequest'));
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
                'clock_out.after' => '出勤時間が不適切な値です',
                'break_end.*.after' => '休憩終了時間は休憩開始時間より後の時間を入力してください。',
                'remark.required' => '備考を記入してください',
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
                            'break_start' => '休憩時間が不適切な値です'
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

        /*$attendance->update([
            'clock_in' => $request->clock_in
                ? Carbon::parse($attendance->work_date . ' ' . $request->clock_in)
                : null,
            'clock_out' => $request->clock_out
                ? Carbon::parse($attendance->work_date . ' ' . $request->clock_out)
                : null,
            'remark' => $request->remark,
        ]);*/
        StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => auth()->id(),

            'clock_in' => $request->clock_in
                ? Carbon::parse($attendance->work_date . ' ' . $request->clock_in)
                : null,

            'clock_out' => $request->clock_out
                ? Carbon::parse($attendance->work_date . ' ' . $request->clock_out)
                : null,

            'remark' => $request->remark,

            'approval_status' => 'pending',
        ]);

        /*foreach ($attendance->breakTimes as $index => $breakTime) {
            $breakTime->update([
                'break_start' => isset($request->break_start[$index])
                    ? Carbon::parse($attendance->work_date . ' ' . $request->break_start[$index])
                    : null,
                'break_end' => isset($request->break_end[$index])
                    ? Carbon::parse($attendance->work_date . ' ' . $request->break_end[$index])
                    : null,
            ]);
        }*/

        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('message', '勤怠情報を修正しました');
    }

    public function requestList(Request $request)
    {
        $status = $request->status ?? 'pending';

        $requests = StampCorrectionRequest::with(['user', 'attendance'])
            ->where('user_id', auth()->id())
            ->where('approval_status', $status)
            ->get();
        return view('attendance.request_list', compact('status', 'requests'));
    }

    public function adminRequestList(Request $request)
    {
        $status = $request->status ?? 'pending';

        $requests = StampCorrectionRequest::with(['user', 'attendance'])
            ->where('approval_status', $status)
            ->get();

        return view('admin.request_list', compact('status', 'requests'));
    }

    public function staffAttendance(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $month = $request->month
            ? Carbon::parse($request->month)
            : now();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $id)
            ->whereYear('work_date', $month->year)
            ->whereMonth('work_date', $month->month)
            ->orderBy('work_date', 'desc')
            ->get();

        $attendances = $attendances->keyBy(function ($attendance) {
            return \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d');
        });

        $days = collect();

        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $days->push($date->copy());
        }

        return view('admin.staff_attendance', compact(
            'user',
            'attendances',
            'month',
            'days'
        ));
    }

    public function adminAttendanceList(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $attendances = Attendance::with(['user', 'breakTimes'])
            ->whereDate('work_date', $date)
            ->orderBy('clock_in')
            ->get();

        return view('admin.attendance_list', compact('attendances', 'date'));
    }

    public function staffList()
    {
        $users = User::where('is_admin', false)->get();

        return view('admin.staff_list', compact('users'));
    }

    public function adminRequestDetail($id)
    {
        $request = StampCorrectionRequest::with(['user', 'attendance'])
            ->findOrFail($id);

        return view('admin.request_detail', compact('request'));
    }

    public function approveRequest($attendance_correct_request_id)
    {
        $request = StampCorrectionRequest::findOrFail($attendance_correct_request_id);

        $attendance = $request->attendance;

        $attendance->update([
            'clock_in' => $request->clock_in,
            'clock_out' => $request->clock_out,
            'remark' => $request->remark,
        ]);

        $request->update([
            'approval_status' => 'approved',
        ]);

        return redirect()
            ->route('admin.requests.detail', $request->id);
    }

    public function adminAttendanceDetail($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])
            ->findOrFail($id);

        return view('admin.staff_attendance_detail', compact('attendance'));
    }

    public function adminAttendanceUpdate(Request $request, $id)
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
                'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
                'break_end.*.after' => '休憩時間が不適切な値です',
                'remark.required' => '備考を記入してください',
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
                            'break_start' => '休憩時間が不適切な値です'
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

        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        $attendance->update([
            'clock_in' => $attendance->work_date . ' ' . $request->clock_in . ':00',
            'clock_out' => $attendance->work_date . ' ' . $request->clock_out . ':00',
            'remark' => $request->remark,
        ]);

        foreach ($attendance->breakTimes as $index => $breakTime) {
            $breakStart = $request->break_start[$index] ?? null;
            $breakEnd = $request->break_end[$index] ?? null;

            $breakTime->update([
                'break_start' => $breakStart
                    ? $attendance->work_date . ' ' . $breakStart . ':00'
                    : null,
                'break_end' => $breakEnd
                    ? $attendance->work_date . ' ' . $breakEnd . ':00'
                    : null,
            ]);
        }

        return redirect()
            ->route('admin.attendance.detail', $attendance->id);
    }

    public function report()
    {
        $startDate = now()->subMonths(5)->startOfMonth();
        $endDate = now()->endOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', auth()->id())
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

        $totalWorkMinutes = 0;
        $totalOvertimeMinutes = 0;
        $workedDays = 0;

        foreach ($attendances as $attendance) {
        if (!$attendance->clock_in || !$attendance->clock_out) {
            continue;
        }

        $breakMinutes = 0;

        foreach ($attendance->breakTimes as $breakTime) {
            if ($breakTime->break_start && $breakTime->break_end) {
                $breakMinutes += Carbon::parse($breakTime->break_start)
                    ->diffInMinutes(Carbon::parse($breakTime->break_end));
            }
        }

        $workMinutes = Carbon::parse($attendance->clock_in)
            ->diffInMinutes(Carbon::parse($attendance->clock_out))
            - $breakMinutes;

        $totalWorkMinutes += $workMinutes;

        $totalOvertimeMinutes += max(0, $workMinutes - 480);
        $workedDays++;
        }

            $monthlyData = [];

            for ($i = 5; $i >= 0; $i--) {
                $month = now()->subMonths($i);

                $monthlyData[$month->format('Y-m')] = [
                    'label' => $month->format('Y-m'),
                    'workMinutes' => 0,
                    'overtimeMinutes' => 0,
                ];
            }

            foreach ($attendances as $attendance) {
                if (!$attendance->clock_in || !$attendance->clock_out) {
                    continue;
                }

            $breakMinutes = 0;

            foreach ($attendance->breakTimes as $breakTime) {
                if ($breakTime->break_start && $breakTime->break_end) {
                    $breakMinutes += Carbon::parse($breakTime->break_start)
                        ->diffInMinutes(Carbon::parse($breakTime->break_end));
                }
            }

            $workMinutes = Carbon::parse($attendance->clock_in)
                ->diffInMinutes(Carbon::parse($attendance->clock_out))
                - $breakMinutes;

            $monthKey = Carbon::parse($attendance->work_date)->format('Y-m');

                if (isset($monthlyData[$monthKey])) {
                    $monthlyData[$monthKey]['workMinutes'] += $workMinutes;
                    $monthlyData[$monthKey]['overtimeMinutes'] += max(0, $workMinutes - 480);
                }
            }

            $lateCount = 0;
            $earlyLeaveCount = 0;
            $longWorkCount = 0;

            $currentMonthAttendances = $attendances->filter(function ($attendance) {
                return Carbon::parse($attendance->work_date)->isSameMonth(now());
            });

            $lateCount = $currentMonthAttendances
                ->filter(function ($attendance) {
                    return $attendance->clock_in
                        && Carbon::parse($attendance->clock_in)->format('H:i') > '09:00';
                })
                ->count();

            $earlyLeaveCount = $currentMonthAttendances
                ->filter(function ($attendance) {
                    return $attendance->clock_out
                        && Carbon::parse($attendance->clock_out)->format('H:i') < '18:00';
                })
                ->count();

            $longWorkCount = $currentMonthAttendances
                ->filter(function ($attendance) {
                    if (!$attendance->clock_in || !$attendance->clock_out) {
                        return false;
                    }

                    $breakMinutes = $attendance->breakTimes
                        ->filter(function ($breakTime) {
                            return $breakTime->break_start && $breakTime->break_end;
                        })
                        ->sum(function ($breakTime) {
                            return Carbon::parse($breakTime->break_start)
                                ->diffInMinutes(Carbon::parse($breakTime->break_end));
                        });

                    $workMinutes = Carbon::parse($attendance->clock_in)
                        ->diffInMinutes(Carbon::parse($attendance->clock_out))
                        - $breakMinutes;

                    return $workMinutes > 600;
                })
                ->count();

        $averageWorkMinutes = $workedDays > 0
            ? intdiv($totalWorkMinutes, $workedDays)
            : 0;

        return view('attendance_report', compact(
            'attendances',
            'totalWorkMinutes',
            'totalOvertimeMinutes',
            'averageWorkMinutes',
            'monthlyData',
            'lateCount',
            'earlyLeaveCount',
            'longWorkCount'
        ));
    }

    public function exportCsv(Request $request, User $user)
    {
        $month = Carbon::parse($request->month ?? now()->format('Y-m'));

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $month->year)
            ->whereMonth('work_date', $month->month)
            ->orderBy('work_date')
            ->get();

        $fileName = $user->name . '_' . $month->format('Y-m') . '_attendance.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $callback = function () use ($attendances) {
            $file = fopen('php://output', 'w');

            // Excelで文字化けしないように
            fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計'
            ]);

            foreach ($attendances as $attendance) {

                $breakMinutes = 0;

                foreach ($attendance->breakTimes as $break) {
                    if ($break->break_start && $break->break_end) {
                        $breakMinutes += Carbon::parse($break->break_start)
                            ->diffInMinutes(Carbon::parse($break->break_end));
                    }
                }

                $workMinutes = 0;

                if ($attendance->clock_in && $attendance->clock_out) {
                    $workMinutes =
                        Carbon::parse($attendance->clock_in)
                            ->diffInMinutes(Carbon::parse($attendance->clock_out))
                        - $breakMinutes;
                }

                fputcsv($file, [
                    Carbon::parse($attendance->work_date)->format('Y/m/d'),

                    $attendance->clock_in
                        ? Carbon::parse($attendance->clock_in)->format('H:i')
                        : '',

                    $attendance->clock_out
                    ? Carbon::parse($attendance->clock_out)->format('H:i')
                        : '',

                    sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60),

                    sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
