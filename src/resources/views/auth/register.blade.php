<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>会員登録</title>
</head>
<body>

    <h1>会員登録</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label>名前</label><br>
            <input type="text" name="name">
        </div>

        <br>

        <div>
            <label>メールアドレス</label><br>
            <input type="email" name="email">
        </div>

        <br>

        <div>
            <label>パスワード</label><br>
            <input type="password" name="password">
        </div>

        <br>

        <div>
            <label>パスワード確認</label><br>
            <input type="password" name="password_confirmation">
        </div>

        <br>

        <button type="submit">登録する</button>

    </form>

    <br>

    <a href="{{ route('login') }}">ログインはこちら</a>

</body>
</html>