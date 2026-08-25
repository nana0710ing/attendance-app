<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_user_name_is_displayed()
    {
        $user = \App\Models\User::factory()->create([
            'name' => '山田太郎',
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->format('Y-m-d'),
            'clock_in' => today()->setTime(9, 0),
            'clock_out' => today()->setTime(18, 0),
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
    }

    public function test_selected_date_is_displayed()
    {
        $user = \App\Models\User::factory()->create();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('8月20日');
    }

    public function test_clock_in_and_clock_out_are_displayed()
    {
        $user = \App\Models\User::factory()->create();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:15:00',
            'clock_out' => '2026-08-20 18:30:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('09:15');
        $response->assertSee('18:30');
    }

    public function test_break_time_is_displayed()
    {
        $user = \App\Models\User::factory()->create();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
        ]);

        \App\Models\BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '2026-08-20 12:00:00',
            'break_end' => '2026-08-20 13:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
