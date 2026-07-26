<h1>ログイン</h1>

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div>
        <label>メールアドレス</label><br>
        <input type="email" name="email" value="{{ old('email') }}">
    </div>

    <br>

    <div>
        <label>パスワード</label><br>
        <input type="password" name="password">
    </div>

    <br>

    <button type="submit">ログインする</button>
</form>

<br>

<a href="{{ route('register') }}">会員登録はこちら</a>