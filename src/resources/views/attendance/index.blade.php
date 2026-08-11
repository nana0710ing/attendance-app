@extends('layouts.staff')

@section('content')

<div class="attendance-container">

    <div class="attendance-status">
        @if (!$attendance)
            勤務外
        @elseif ($attendance->status === '出勤中')
            出勤中
        @elseif ($attendance->status === '休憩中')
            休憩中
        @elseif ($attendance->status === '退勤済')
            退勤済
        @endif
    </div>

    <p class="attendance-date">
        {{ now()->format('Y年n月j日') }}({{ ['日','月','火','水','木','金','土'][now()->dayOfWeek] }})
    </p>

    <p class="attendance-time">
        {{ now()->format('H:i') }}
    </p>

    <div class="attendance-actions">

        @if (!$attendance)

            <form method="POST" action="{{ route('attendance.clock-in') }}">
                @csrf
                <button class="attendance-button" type="submit">出勤</button>
            </form>

        @elseif ($attendance->status === '出勤中')

            <form method="POST" action="{{ route('attendance.clock-out') }}">
                @csrf
                <button class="attendance-button" type="submit">退勤</button>
            </form>

            <form method="POST" action="{{ route('attendance.break-start') }}">
                @csrf
                <button class="attendance-button break-button" type="submit">休憩入</button>
            </form>

        @elseif ($attendance->status === '休憩中')

            <form method="POST" action="{{ route('attendance.break-end') }}">
                @csrf
                <button class="attendance-button break-button" type="submit">休憩戻</button>
            </form>

        @elseif ($attendance->status === '退勤済')

            <p class="attendance-finished">お疲れ様でした。</p>

        @endif

    </div>

</div>

@endsection