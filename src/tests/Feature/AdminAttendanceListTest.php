<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_all_users_attendance_for_the_day_is_displayed()
    {
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $user1 = \App\Models\User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $user2 = \App\Models\User::factory()->create([
            'name' => 'テスト花子',
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2026-08-25',
            'clock_in' => '2026-08-25 09:00:00',
            'clock_out' => '2026-08-25 18:00:00',
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-08-25',
            'clock_in' => '2026-08-25 10:00:00',
            'clock_out' => '2026-08-25 19:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2026-08-25');

        $response->assertStatus(200);

        $response->assertSee('テスト太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('テスト花子');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    public function test_current_date_is_displayed()
    {
        \Carbon\Carbon::setTestNow('2026-08-25 10:00:00');

        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026年08月25日');

        \Carbon\Carbon::setTestNow();
    }

    public function test_previous_day_attendance_is_displayed()
    {
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $user = \App\Models\User::factory()->create([
            'name' => '前日ユーザー',
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-24',
            'clock_in' => '2026-08-24 09:00:00',
            'clock_out' => '2026-08-24 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2026-08-24');

        $response->assertStatus(200);
        $response->assertSee('2026年08月24日');
        $response->assertSee('前日ユーザー');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_next_day_attendance_is_displayed()
    {
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $user = \App\Models\User::factory()->create([
            'name' => '翌日ユーザー',
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-26',
            'clock_in' => '2026-08-26 09:00:00',
            'clock_out' => '2026-08-26 18:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2026-08-26');

        $response->assertStatus(200);
        $response->assertSee('2026年08月26日');
        $response->assertSee('翌日ユーザー');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
