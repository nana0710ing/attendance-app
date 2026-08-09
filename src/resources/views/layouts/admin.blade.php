<!DOCTYPE html>
<html lang="ja">
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite(['resources/css/admin.css'])

    <title>管理画面</title>
</head>

<body>

<header>
    <div class="logo">
        <img src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
    </div>

    <nav>
        <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>

        <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>

        <a href="{{ route('admin.requests') }}">申請一覧</a>

        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
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