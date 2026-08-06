<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠詳細</title>
</head>

<body>
    <h1>勤怠詳細</h1>

    @if ($errors->any())
    <div>
        <ul>
            @foreach ($errors->all() as $error)
                <li style="color: red;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form method="POST" action="{{ route('attendance.update', $attendance->id) }}">
        @csrf
        @method('PATCH')

    <table>
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
        <p>※承認待ちの申請があります。</p>
    @else
        <button type="submit">修正</button>
    @endif
    </form>
</body>

</html>