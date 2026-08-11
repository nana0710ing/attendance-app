<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/staff.css'])

    <title>勤怠管理</title>
</head>

<body>

<header class="staff-header">
    <div class="staff-header__logo">
        <img src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
    </div>

    <nav class="staff-header__nav">
        <a href="{{ url('/attendance') }}">勤怠</a>
        <a href="{{ route('attendance.list') }}">勤怠一覧</a>
        <a href="{{ route('request.list') }}">申請</a>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit">ログアウト</button>
        </form>
    </nav>
</header>

<main>
    @yield('content')
</main>

</body>
</html>