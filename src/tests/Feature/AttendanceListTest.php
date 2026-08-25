<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_own_attendance_records_are_displayed()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDays(2)->toDateString(),
            'clock_in' => today()->subDays(2)->setTime(9, 0),
            'clock_out' => today()->subDays(2)->setTime(18, 0),
            'status' => '退勤済',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today()->subDay()->toDateString(),
            'clock_in' => today()->subDay()->setTime(10, 0),
            'clock_out' => today()->subDay()->setTime(17, 0),
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('17:00');
    }

    public function test_current_month_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('value="' . now()->format('Y-m') . '"', false);
    }

    public function test_previous_month_is_displayed()
    {
        $user = \App\Models\User::factory()->create();

        $previousMonth = now()->subMonth();

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => $previousMonth->copy()->startOfMonth()->format('Y-m-d'),
            'clock_in' => $previousMonth->copy()->startOfMonth()->setTime(9, 0),
            'clock_out' => $previousMonth->copy()->startOfMonth()->setTime(18, 0),
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)->get(
            '/attendance/list?month=' . $previousMonth->format('Y-m')
        );

        $response->assertStatus(200);

        $response->assertSee(
            'value="' . $previousMonth->format('Y-m') . '"',
            false
        );

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_next_month_is_displayed()
    {
        $user = \App\Models\User::factory()->create();

        $nextMonth = now()->addMonth();

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => $nextMonth->copy()->startOfMonth()->format('Y-m-d'),
            'clock_in' => $nextMonth->copy()->startOfMonth()->setTime(9, 0),
            'clock_out' => $nextMonth->copy()->startOfMonth()->setTime(18, 0),
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)->get(
            '/attendance/list?month=' . $nextMonth->format('Y-m')
        );

        $response->assertStatus(200);

        $response->assertSee(
            'value="' . $nextMonth->format('Y-m') . '"',
            false
        );

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_detail_link_displays_selected_attendance_detail()
    {
        $user = \App\Models\User::factory()->create();

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
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
