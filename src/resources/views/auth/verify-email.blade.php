<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証</title>
    <style>
        body {
            margin: 0;
        }

        .header {
            height: 80px;
            background: #000;
            display: flex;
            align-items: center;
            padding: 0 40px;
            box-sizing: border-box;
        }

        .header img {
            width: 370px;
            height: auto;
            object-fit: contain;
        }
    </style>
    <style>
        body {
            margin: 0;
        }

        .verify-content {
            text-align: center;
            margin-top: 180px;
        }

        .verify-button {
            display: inline-block;
            margin-top: 35px;
            padding: 18px 30px;
            background-color: #d9d9d9;
            border: 1px solid #000;
            border-radius: 8px;
            color: #000;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
        }

        .verify-content form {
            margin-top: 50px;
        }

        .verify-content form button {
            border: none;
            background: none;
            color: #0073cc;
            font-size: 16px;
            cursor: pointer;
        }

        .verify-content p {
            font-size: 18px;
            font-weight: bold;
            line-height: 1.5;
        }
    </style>
</head>
<body>
<header class="header">
    <img src="{{ asset('images/coachtech-logo.png') }}" alt="COACHTECH">
</header>

<div class="verify-content">
    <p>
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    <a href="http://localhost:8025" class="verify-button">
    認証はこちらから
    </a>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">認証メールを再送する</button>
    </form>
</div>

</body>
</html>