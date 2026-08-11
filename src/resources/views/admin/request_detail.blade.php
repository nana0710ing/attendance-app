@extends('layouts.admin')

@section('content')

<h1 class="page-title">勤怠詳細</h1>

<div class="request-detail-card">

    <div class="request-detail-row">
        <div class="request-detail-label">名前</div>
        <div class="request-detail-value">
            {{ $request->user->name }}
        </div>
    </div>

    <div class="request-detail-row">
        <div class="request-detail-label">日付</div>
        <div class="request-detail-value">
            {{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年m月d日') }}
        </div>
    </div>

    <div class="request-detail-row">
        <div class="request-detail-label">出勤・退勤</div>
        <div class="request-detail-value">
            {{ \Carbon\Carbon::parse($request->clock_in)->format('H:i') }}
            <span>〜</span>
            {{ \Carbon\Carbon::parse($request->clock_out)->format('H:i') }}
        </div>
    </div>

    @foreach ($request->attendance->breakTimes as $index => $break)
        <div class="request-detail-row">
            <div class="request-detail-label">
                休憩{{ $index > 0 ? $index + 1 : '' }}
            </div>

            <div class="request-detail-value">
                {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}
                <span>〜</span>

                @if ($break->break_end)
                    {{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}
                @endif
            </div>
        </div>
    @endforeach

    <div class="request-detail-row">
        <div class="request-detail-label">備考</div>
        <div class="request-detail-value">
            {{ $request->remark }}
        </div>
    </div>

</div>

@if ($request->approval_status === 'pending')
    <form class="approval-form"
        action="{{ route('admin.requests.approve', $request->id) }}"
        method="POST">
        @csrf
        @method('PATCH')

        <button class="approval-button" type="submit">承認</button>
    </form>
@else
    <button class="approval-button approved" type="button" disabled>
        承認済み
    </button>
@endif
@endsection