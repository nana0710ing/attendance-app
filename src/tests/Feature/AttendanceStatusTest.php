<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_is_off_duty()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_status_is_working()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::today()->setTime(9, 0, 0),
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_status_is_on_break()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in' => Carbon::today()->setTime(9, 0, 0),
            'status' => '休憩中',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_status_is_finished()
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
        $response->assertSee('退勤済');
    }
}