<h1>打刻画面</h1>
<p>{{ now()->format('Y年n月j日') }}</p>
<p>{{ now()->format('H:i') }}</p>

@if (!$attendance)
    <form method="POST" action="{{ route('attendance.clock-in') }}">
        @csrf
        <button type="submit">出勤</button>
    </form>

@elseif ($attendance->status === '出勤中')
    <form method="POST" action="{{ route('attendance.break-start') }}">
        @csrf
        <button type="submit">休憩入</button>
    </form>

    <form method="POST" action="{{ route('attendance.clock-out') }}">
        @csrf
        <button type="submit">退勤</button>
    </form>

@elseif ($attendance->status === '休憩中')
    <form method="POST" action="{{ route('attendance.break-end') }}">
        @csrf
        <button type="submit">休憩戻</button>
    </form>

@elseif ($attendance->status === '退勤済')
    <p>お疲れ様でした。</p>
@endif