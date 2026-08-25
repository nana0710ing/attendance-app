<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_out()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_in' => now(),
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($user)
            ->post('/attendance/clock-out');

        $response->assertRedirect('/attendance');

        $attendance->refresh();

        $this->assertEquals('退勤済', $attendance->status);
        $this->assertNotNull($attendance->clock_out);
    }

    public function test_clock_out_time_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_in' => today()->setTime(9, 0, 0),
            'status' => '出勤中',
        ]);

        $this->actingAs($user)
            ->post('/attendance/clock-out');

        $attendance->refresh();

        $clockOut = \Carbon\Carbon::parse($attendance->clock_out)->format('H:i');

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($clockOut);
    }
}
