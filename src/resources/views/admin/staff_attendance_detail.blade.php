@extends('layouts.admin')

@section('content')

<h1 class="page-title">勤怠詳細</h1>

@if ($errors->any())
    <div>
        <ul>
            @foreach (array_unique($errors->all()) as $error)
                <li style="color: red;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<div class="attendance-detail">

<form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
    @csrf
    @method('PATCH')

    <div class="detail-row">
        <span class="label">氏名</span>
        <span>{{ $attendance->user->name }}</span>
    </div>

    <div class="detail-row">
        <span class="label">日付</span>
        <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日') }}</span>
    </div>

<div class="detail-row">
    <span class="label">出勤・退勤</span>

    <input type="time"
        name="clock_in"
        value="{{ $attendance->clock_in
            ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
            : '' }}">

    <span>～</span>

    <input type="time"
        name="clock_out"
        value="{{ $attendance->clock_out
            ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
            : '' }}">
</div>

@foreach ($attendance->breakTimes as $index => $breakTime)
    <div class="detail-row">
        <span class="label">休憩{{ $index + 1 }}</span>

        <input type="time"
            name="break_start[]"
            value="{{ $breakTime->break_start
                ? \Carbon\Carbon::parse($breakTime->break_start)->format('H:i')
                : '' }}">

        <span>～</span>

        <input type="time"
            name="break_end[]"
            value="{{ $breakTime->break_end
                ? \Carbon\Carbon::parse($breakTime->break_end)->format('H:i')
                : '' }}">
    </div>
@endforeach

<div class="detail-row">
    <span class="label">備考</span>

    <textarea name="remark">{{ old('remark', $attendance->remark) }}</textarea>
</div>
<button type="submit" class="detail-button">修正</button>

</form>
</div>

@endsection