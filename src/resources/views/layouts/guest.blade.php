<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    @vite(['resources/css/guest.css'])
</head>

<body>
    <header class="guest-header">
        <img
            src="{{ asset('images/coachtech-logo.png') }}"
            alt="COACHTECH"
            class="guest-header__logo"
        >
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>