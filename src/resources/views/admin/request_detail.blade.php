<h1>申請詳細</h1>

<p>氏名：{{ $request->user->name }}</p>

<p>対象日：{{ $request->attendance->work_date }}</p>

<p>出勤：{{ \Carbon\Carbon::parse($request->clock_in)->format('H:i') }}</p>

<p>退勤：{{ \Carbon\Carbon::parse($request->clock_out)->format('H:i') }}</p>

<p>備考：{{ $request->remark }}</p>

@if ($request->approval_status === 'pending')
    <form action="{{ route('admin.requests.approve', $request->id) }}" method="POST">
        @csrf
        @method('PATCH')

        <button type="submit">承認</button>
    </form>
@else
    <button type="button" disabled>承認済み</button>
@endif