<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use Carbon\Carbon;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_in()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中');
    }

    public function test_user_cannot_clock_in_twice_in_one_day()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::today()->setTime(9, 0, 0),
            'clock_out' => Carbon::today()->setTime(18, 0, 0),
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertDontSee('>出勤<', false);
    }

    public function test_clock_in_time_is_displayed_on_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 25, 9, 30, 0));

        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:30');

        Carbon::setTestNow();
    }
}