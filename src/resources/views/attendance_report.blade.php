@extends('layouts.staff')

@section('content')

<div class="report-container">

<h1>マイ勤怠レポート</h1>

<p>過去6ヶ月の勤怠データから集計しています。</p>

<h2>基本サマリー</h2>

<div class="summary-cards">

    <div>
        <p>総労働時間</p>
        <strong>
            {{ floor($totalWorkMinutes / 60) }}h
            {{ $totalWorkMinutes % 60 }}m
        </strong>
    </div>

    <div>
        <p>総残業時間</p>
        <strong>
            {{ floor($totalOvertimeMinutes / 60) }}h
            {{ $totalOvertimeMinutes % 60 }}m
        </strong>
    </div>

    <div>
        <p>平均労働時間 / 日</p>
        <strong>
            {{ floor($averageWorkMinutes / 60) }}h
            {{ $averageWorkMinutes % 60 }}m
        </strong>
    </div>
</div>

<h2>月次推移（過去６ヶ月）</h2>

<table class="monthly-table">
    <thead>
        <tr>
            <th>月</th>
            <th>労働時間</th>
            <th>残業時間</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($monthlyData as $month)
            <tr>
                <td>{{ $month['label'] }}</td>
                <td>
                    {{ floor($month['workMinutes'] / 60) }}h
                    {{ $month['workMinutes'] % 60 }}m
                </td>
                <td>
                    {{ floor($month['overtimeMinutes'] / 60) }}h
                    {{ $month['overtimeMinutes'] % 60 }}m
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<h2>今月の異常検知</h2>

<p>基準：始業 09:00 / 終業 18:00 / 長時間労働は1日10時間超</p>

<div class="alert-cards">

<div>
    <p>遅刻回数</p>
    <strong>{{ $lateCount }}回</strong>
</div>

<div>
    <p>早退回数</p>
    <strong>{{ $earlyLeaveCount }}回</strong>
</div>

<div>
    <p>長時間労働日数</p>
    <strong>{{ $longWorkCount }}日</strong>
</div>
</div>
@endsection

<style>
.report-container {
    width: 70%;
    max-width: 1000px;
    margin: 60px auto;
}

.summary-cards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.summary-cards > div {
    flex: 1;
    padding: 18px 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.summary-cards p {
    margin: 0 0 10px;
    font-size: 14px;
    color: #666;
}

.summary-cards strong {
    font-size: 22px;
}

.monthly-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 35px;
}

.monthly-table th,
.monthly-table td {
    padding: 15px 20px;
    text-align: center;
    border-bottom: 1px solid #ddd;
}

.monthly-table th {
    color: #666;
    font-weight: bold;
}

.monthly-table td {
    font-weight: 600;
}

.monthly-table tr:last-child td {
    border-bottom: none;
}

.alert-cards {
    display: flex;
    gap: 22px;
    margin-top: 20px;
}

.alert-cards > div {
    flex: 1;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 22px;
}

.alert-cards p {
    margin: 0 0 12px;
    color: #666;
}

.alert-cards strong {
    font-size: 22px;
}
</style>