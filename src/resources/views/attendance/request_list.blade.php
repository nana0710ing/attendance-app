<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申請一覧</title>
</head>

<body>
    <h1>申請一覧</h1>

    <div>
        <a href="{{ route('request.list', ['status' => 'pending']) }}">
            承認待ち
        </a>

        @if($status === 'pending')
            ←
        @endif

        <a href="{{ route('request.list', ['status' => 'approved']) }}">
            承認済み
        </a>

        @if($status === 'approved')
            ←
        @endif
    </div>

    <table border="1">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>

    <tbody>
    @foreach ($attendances as $attendance)
    <tr>
        <td>{{ $attendance->approval_status }}</td>
        <td>{{ $attendance->user->name }}</td>
        <td>{{ $attendance->work_date }}</td>
        <td>{{ $attendance->remark }}</td>
        <td>{{ $attendance->updated_at->format('Y/m/d') }}</td>
        <td>
            <a href="{{ route('attendance.detail', $attendance->id) }}">詳細</a>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
</body>

</html>