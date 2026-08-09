@extends('layouts.admin')

@section('content')

<h1 class="page-title">勤怠詳細</h1>

<div class="attendance-detail">

    <div class="detail-row">
        <span class="label">氏名</span>
        <span>{{ $attendance->user->name }}</span>
    </div>

    <div class="detail-row">
        <span class="label">日付</span>
        <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日') }}</span>
    </div>

<div class="detail-row">
    <span class="label">出勤</span>
    <span>
        {{ $attendance->clock_in
            ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
            : '' }}
    </span>
</div>

<div class="detail-row">
    <span class="label">退勤</span>
    <span>
        {{ $attendance->clock_out
            ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
            : '' }}
    </span>
</div>

@foreach ($attendance->breakTimes as $index => $breakTime)
    <div class="detail-row">
        <span class="label">休憩{{ $index + 1 }}</span>
        <span>
            {{ $breakTime->break_start
                ? \Carbon\Carbon::parse($breakTime->break_start)->format('H:i')
                : '' }}
            ～
            {{ $breakTime->break_end
                ? \Carbon\Carbon::parse($breakTime->break_end)->format('H:i')
                : '' }}
        </span>
    </div>
@endforeach

<div class="detail-row">
    <span class="label">備考</span>
    <span>{{ $attendance->remark }}</span>
</div>

</div>

@endsection