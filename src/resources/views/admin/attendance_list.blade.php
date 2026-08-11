@extends('layouts.admin')

@section('content')

<h1 class="page-title">
    {{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}の勤怠
</h1>

<div class="date-nav">
    <a href="{{ route('admin.attendance.list', [
        'date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')
    ]) }}">
        ← 前日
    </a>

    <form class="date-picker"
        method="GET"
        action="{{ route('admin.attendance.list') }}">
        <input
            class="date-input"
            type="date"
            name="date"
            value="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
            onchange="this.form.submit()"
        >
    </form>

    <a href="{{ route('admin.attendance.list', [
        'date' => \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d')
    ]) }}">
        翌日 →
    </a>
</div>

<br>

<table class="attendance-table">
    <thead>
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->user->name }}</td>

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
                        $breakMinutes = 0;

                        foreach ($attendance->breakTimes as $breakTime) {
                            if ($breakTime->break_start && $breakTime->break_end) {
                                $breakMinutes += \Carbon\Carbon::parse($breakTime->break_start)
                                    ->diffInMinutes(\Carbon\Carbon::parse($breakTime->break_end));
                            }
                        }

                        echo floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT);
                    @endphp
                </td>
                <td>
                    @php
                        if ($attendance->clock_in && $attendance->clock_out) {
                            $workMinutes =
                                \Carbon\Carbon::parse($attendance->clock_in)
                                    ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out))
                                    - $breakMinutes;

                            echo floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT);
                        }
                    @endphp
                </td>

                <td>
                    <a href="{{ route('admin.attendance.detail', $attendance->id) }}">
                        詳細
                    </a>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
@endsection