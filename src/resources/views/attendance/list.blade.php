@php
    \Carbon\Carbon::setLocale('ja');
@endphp

<h1>勤怠一覧</h1>
<div>
    <a href="{{ route('attendance.list', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}">
        ← 前月
    </a>

    <span>{{ $currentMonth->format('Y/m') }}</span>

    <a href="{{ route('attendance.list', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}">
        翌月 →
    </a>
</div>
<table border="1">
    <tr>
        <th>日付</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>休憩</th>
        <th>詳細</th>
    </tr>

    @foreach ($attendances as $attendance)
        <tr>
            <td>
                {{ \Carbon\Carbon::parse($attendance->work_date)->isoFormat('MM/DD(ddd)') }}
            </td>
            <td>
                {{ $attendance->clock_in
                    ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                    : '' }}
            </td>
            <td>
                {{ $attendance->clock_out
                    ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                    : '' }}
            </td>

            <td>
                @php
                    $totalBreak = 0;

                foreach ($attendance->breakTimes as $breakTime) {
                    if ($breakTime->break_start && $breakTime->break_end) {
                        $totalBreak += \Carbon\Carbon::parse($breakTime->break_start)
                            ->diffInMinutes(\Carbon\Carbon::parse($breakTime->break_end));
                    }
                }

                echo floor($totalBreak / 60) . ':' . str_pad($totalBreak % 60, 2, '0', STR_PAD_LEFT);
                @endphp
            </td>

            <td>
                <a href="{{ route('attendance.detail', $attendance->id) }}">
                    詳細
                </a>
            </td>
        </tr>
        @endforeach
</table>