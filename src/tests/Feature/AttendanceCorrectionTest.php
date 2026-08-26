<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_error_when_clock_in_is_after_clock_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->patch('/attendance/detail/' . $attendance->id, [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'remark' => '修正テスト',
            ]);

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間が不適切な値です',
        ]);
    }

    public function test_error_when_break_start_is_after_clock_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->patch('/attendance/detail/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['19:00'],
                'break_end' => ['19:30'],
                'remark' => '修正テスト',
            ]);

        $response->assertSessionHasErrors([
            'break_start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_error_when_break_end_is_after_clock_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->patch('/attendance/detail/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['12:00'],
                'break_end' => ['19:00'],
                'remark' => '修正テスト',
            ]);

        $response->assertSessionHasErrors([
            'break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_error_when_remark_is_empty()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->patch('/attendance/detail/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'remark' => '',
            ]);

        $response->assertSessionHasErrors([
            'remark' => '備考を記入してください',
        ]);
    }

    public function test_correction_request_is_created()
    {
        $user = \App\Models\User::factory()->create();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
        ]);

        $response = $this->actingAs($user)->patch(
            '/attendance/detail/' . $attendance->id,
            [
                'clock_in' => '09:30',
                'clock_out' => '18:30',
                'remark' => '修正テスト',
            ]
        );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'remark' => '修正テスト',
            'approval_status' => 'pending',
        ]);
    }

    public function test_pending_requests_are_displayed()
    {
        $user = \App\Models\User::factory()->create();

        $attendance1 = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
        ]);

        $attendance2 = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-21',
            'clock_in' => '2026-08-21 09:00:00',
            'clock_out' => '2026-08-21 18:00:00',
        ]);

        \App\Models\StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user->id,
            'clock_in' => '2026-08-20 09:30:00',
            'clock_out' => '2026-08-20 18:30:00',
            'remark' => '申請テスト1',
            'approval_status' => 'pending',
        ]);

        \App\Models\StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user->id,
            'clock_in' => '2026-08-21 10:00:00',
            'clock_out' => '2026-08-21 19:00:00',
            'remark' => '申請テスト2',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);

        $response->assertSee('申請テスト1');
        $response->assertSee('申請テスト2');
    }

    public function test_approved_requests_are_displayed()
    {
        $user = \App\Models\User::factory()->create();

        $attendance1 = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
        ]);

        $attendance2 = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-21',
            'clock_in' => '2026-08-21 09:00:00',
            'clock_out' => '2026-08-21 18:00:00',
        ]);

        \App\Models\StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user->id,
            'clock_in' => '2026-08-20 09:30:00',
            'clock_out' => '2026-08-20 18:30:00',
            'remark' => '承認済みテスト1',
            'approval_status' => 'approved',
        ]);

        \App\Models\StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user->id,
            'clock_in' => '2026-08-21 10:00:00',
            'clock_out' => '2026-08-21 19:00:00',
            'remark' => '承認済みテスト2',
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);

        $response->assertSee('承認済みテスト1');
        $response->assertSee('承認済みテスト2');
    }

    public function test_correction_request_detail_displays_attendance_detail()
    {
        $user = \App\Models\User::factory()->create();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
        ]);

        \App\Models\StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => '2026-08-20 09:30:00',
            'clock_out' => '2026-08-20 18:30:00',
            'remark' => '詳細確認テスト',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('承認待ちのため修正はできません。');
    }
}