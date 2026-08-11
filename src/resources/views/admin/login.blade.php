@extends('layouts.guest')

@section('title', '管理者ログイン')

@section('content')

<div class="login-container">

    <h1 class="login-title">管理者ログイン</h1>

    <form method="POST" action="{{ route('admin.login.submit') }}" class="login-form">
        @csrf

        <div class="login-form__group">
            <label for="email">メールアドレス</label>
            <input
                id="email"
                type="email"
                name="email"
            >
        </div>

        <div class="login-form__group">
            <label for="password">パスワード</label>
            <input
                id="password"
                type="password"
                name="password"
            >
        </div>

        <button type="submit" class="login-button">
            管理者ログインする
        </button>
    </form>

</div>

@endsection