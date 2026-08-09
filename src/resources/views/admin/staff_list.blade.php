@extends('layouts.admin')

@section('content')

<h1 class="page-title">スタッフ一覧</h1>

<table class="attendance-table">
    <tr>
        <th>名前</th>
        <th>メールアドレス</th>
        <th>詳細</th>
    </tr>

    @foreach ($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <a href="{{ route('admin.staff.attendance', $user->id) }}">詳細</a>
            </td>
        </tr>
    @endforeach

</table>

@endsection