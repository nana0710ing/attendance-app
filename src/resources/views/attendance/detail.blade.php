@extends('layouts.staff')

@section('content')
    <div class="attendance-detail-container">
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

    <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
        @csrf
        @method('PATCH')

    <table class="attendance-detail-table">
        <tr>
            <th>名前</th>
            <td>{{ auth()->user()->name }}</td>
        </tr>

        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年m月d日') }}</td>
        </tr>

        <tr>
            <th>出勤・退勤</th>
            <td>
                <input
                    type="time"
                    name="clock_in"
                    value="{{ $attendance->clock_in
                        ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
                        : '' }}"
        >

        〜

                <input
                    type="time"
                    name="clock_out"
                    value="{{ $attendance->clock_out
                        ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
                        : '' }}"
        >
            </td>
        </tr>

        @foreach ($attendance->breakTimes as $index => $breakTime)
            <tr>
                <th>休憩{{ $index + 1 }}</th>
                <td>
                    <input
                        type="time"
                        name="break_start[]"
                        value="{{ $breakTime->break_start
                            ? \Carbon\Carbon::parse($breakTime->break_start)->format('H:i')
                            : '' }}"
                >

                ～

                    <input
                        type="time"
                        name="break_end[]"
                        value="{{ $breakTime->break_end
                            ? \Carbon\Carbon::parse($breakTime->break_end)->format('H:i')
                            : '' }}"
                >
                </td>
            </tr>
        @endforeach

        <tr>
            <th>備考</th>
            <td>
                <textarea name="remark">{{ old('remark', $attendance->remark ?? '') }}</textarea>
            </td>
        </tr>
    </table>

    @if ($pendingRequest)
        <p class="pending-message">
            *承認待ちのため修正はできません。
        </p>
    @else
        <button class="attendance-detail-button" type="submit">修正</button>
    @endif
    </form>
    </div>
@endsection