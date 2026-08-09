@extends('layouts.admin')

@section('content')

<h1 class="page-title">{{ $user->name }}さんの勤怠</h1>

<div class="date-nav">
    <a href="{{ route('admin.staff.attendance', [
        'id' => $user->id,
        'month' => $month->copy()->subMonth()->format('Y-m')
    ]) }}">
        ← 前月
    </a>

    <strong class="current-month">
        🗓️ {{ $month->format('Y/m') }}
    </strong>

    <a href="{{ route('admin.staff.attendance', [
        'id' => $user->id,
        'month' => $month->copy()->addMonth()->format('Y-m')
    ]) }}">
        次月 →
    </a>
</div>

<br>

<table class="attendance-table">
    <thead>
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($days as $day)
            @php
                $attendance = $attendances->get($day->format('Y-m-d'));
                $breakMinutes = 0;

            if ($attendance) {
                foreach ($attendance->breakTimes as $breakTime) {
                    if ($breakTime->break_start && $breakTime->break_end) {
                        $breakMinutes += \Carbon\Carbon::parse($breakTime->break_start)
                            ->diffInMinutes(\Carbon\Carbon::parse($breakTime->break_end));
                    }
                }
            }
        @endphp

        <tr>
            <td>
                {{ $day->format('m/d') }}
                ({{ ['日','月','火','水','木','金','土'][$day->dayOfWeek] }})
            </td>

            <td>
                {{ $attendance && $attendance->clock_in
                    ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                    : '' }}
            </td>

            <td>
                {{ $attendance && $attendance->clock_out
                    ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                    : '' }}
            </td>

            <td>
                @if ($attendance)
                    {{ floor($breakMinutes / 60) }}:{{ str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                @endif
            </td>

            <td>
                @if ($attendance && $attendance->clock_in && $attendance->clock_out)
                    @php
                        $workMinutes =
                            \Carbon\Carbon::parse($attendance->clock_in)
                                ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out))
                            - $breakMinutes;
                    @endphp

                    {{ floor($workMinutes / 60) }}:{{ str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                @endif
            </td>

            <td>
                @if ($attendance)
                    <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="csv-area">
    <a class="csv-button" href="{{ route('admin.staff.csv', [
        'user' => $user->id,
        'month' => $month->format('Y-m')
    ]) }}">
        CSV出力
    </a>
</div>
@endsection