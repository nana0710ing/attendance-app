@extends('layouts.admin')

@section('content')

    <h1 class="page-title">申請一覧</h1>

    <div class="request-tabs">
        <a href="{{ route('admin.requests', ['status' => 'pending']) }}">
            承認待ち
        </a>

        <a href="{{ route('admin.requests', ['status' => 'approved']) }}">
            承認済み
        </a>
    </div>

    <table class="request-table">
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
            @foreach ($requests as $requestItem)
                <tr>
                    <td>
                        @if ($requestItem->approval_status === 'pending')
                            承認待ち
                        @else
                            承認済み
                        @endif
                    </td>

                    <td>{{ $requestItem->user->name }}</td>

                    <td>{{ $requestItem->attendance->work_date }}</td>

                    <td>{{ $requestItem->remark }}</td>

                    <td>{{ $requestItem->created_at->format('Y/m/d') }}</td>

                    <td>
                        <a href="{{ route('admin.requests.detail', $requestItem->id) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

@endsection