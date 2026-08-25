<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_attendance_data_is_displayed()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:15:00',
            'clock_out' => '2026-08-20 18:30:00',
            'status' => '退勤済',
            'remark' => '管理者詳細テスト',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:15');
        $response->assertSee('18:30');
        $response->assertSee('管理者詳細テスト');
    }

    public function test_error_when_clock_in_is_after_clock_out()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
            'remark' => '管理者修正テスト',
        ]);

        $response = $this->actingAs($admin)
            ->patch('/admin/attendance/' . $attendance->id, [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'remark' => '管理者修正テスト',
            ]);

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_error_when_break_start_is_after_clock_out()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
            'remark' => '管理者修正テスト',
        ]);

        $response = $this->actingAs($admin)
            ->patch('/admin/attendance/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['19:00'],
                'break_end' => ['19:30'],
                'remark' => '管理者修正テスト',
            ]);

        $response->assertSessionHasErrors([
            'break_start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_error_when_break_end_is_after_clock_out()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
            'remark' => '管理者修正テスト',
        ]);

        $response = $this->actingAs($admin)
            ->patch('/admin/attendance/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['12:00'],
                'break_end' => ['19:00'],
                'remark' => '管理者修正テスト',
            ]);

        $response->assertSessionHasErrors([
            'break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_error_when_remark_is_empty()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
            'remark' => '管理者修正テスト',
        ]);

        $response = $this->actingAs($admin)
            ->patch('/admin/attendance/' . $attendance->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => ['12:00'],
                'break_end' => ['13:00'],
                'remark' => '',
            ]);

        $response->assertSessionHasErrors([
            'remark' => '備考を記入してください',
        ]);
    }
}
