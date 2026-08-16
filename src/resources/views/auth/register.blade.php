@extends('layouts.guest')

@section('title', '会員登録')

@section('content')

    <div class="register-container">
        <h1 class="register-title">会員登録</h1>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="register-form__group">
            <label>名前</label>
            <input type="text" name="name">
        </div>

        <br>

        <div class="register-form__group">
            <label>メールアドレス</label><br>
            <input type="email" name="email">
        </div>

        <br>

        <div class="register-form__group">
            <label>パスワード</label><br>
            <input type="password" name="password">
        </div>

        <br>

        <div>
            <div class="register-form__group">
            <label>パスワード確認</label>
            <input type="password" name="password_confirmation">
        </div>

        <br>

        <button class="register-button" type="submit">登録する</button>

    </form>

    <br>

    <a class="login-link" href="{{ route('login') }}">ログインはこちら</a>

    </div>

@endsection