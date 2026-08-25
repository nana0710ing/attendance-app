<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_start_break()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_in' => now(),
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/break-start');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '休憩中',
        ]);
    }

    public function test_user_can_take_break_multiple_times()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_in' => now(),
            'status' => '出勤中',
        ]);

        // 1回目の休憩
        $this->actingAs($user)
            ->post('/attendance/break-start');

        $this->actingAs($user)
            ->post('/attendance/break-end');

        // 2回目の休憩
        $response = $this->actingAs($user)
            ->post('/attendance/break-start');

        $response->assertRedirect('/attendance');

        $attendance->refresh();

        $this->assertEquals('休憩中', $attendance->status);
        $this->assertCount(2, $attendance->breakTimes);
    }

    public function test_user_can_end_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_in' => now(),
            'status' => '出勤中',
        ]);

        // 休憩に入る
        $this->actingAs($user)
            ->post('/attendance/break-start');

        // 休憩から戻る
        $response = $this->actingAs($user)
            ->post('/attendance/break-end');

        $response->assertRedirect('/attendance');

        $attendance->refresh();

        $this->assertEquals('出勤中', $attendance->status);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
        ]);
    }

    public function test_user_can_end_break_multiple_times()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_in' => now(),
            'status' => '出勤中',
        ]);

        // 1回目
        $this->actingAs($user)
            ->post('/attendance/break-start');

        $this->actingAs($user)
            ->post('/attendance/break-end');

        // 2回目
        $this->actingAs($user)
            ->post('/attendance/break-start');

        $response = $this->actingAs($user)
            ->post('/attendance/break-end');

        $response->assertRedirect('/attendance');

        $attendance->refresh();

        // 2回目もちゃんと休憩から戻れている
        $this->assertEquals('出勤中', $attendance->status);

        // 休憩が2件記録されている
        $this->assertCount(2, $attendance->breakTimes);

        foreach ($attendance->breakTimes as $breakTime) {
            $this->assertNotNull($breakTime->break_end);
        }
    }

    public function test_break_time_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_in' => today()->setTime(9, 0, 0),
            'status' => '出勤中',
        ]);

        $this->actingAs($user)->post('/attendance/break-start');

        $breakTime = $attendance->breakTimes()->latest()->first();

        $breakTime->update([
            'break_start' => today()->setTime(12, 0, 0),
            'break_end' => today()->setTime(13, 0, 0),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('1:00');
    }
}
