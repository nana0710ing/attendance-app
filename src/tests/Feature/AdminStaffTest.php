<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_general_users_are_displayed()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'is_admin' => false,
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'sato@example.com',
            'is_admin' => false,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/staff/list');

        $response->assertStatus(200);

        $response->assertSee('山田太郎');
        $response->assertSee('yamada@example.com');
        $response->assertSee('佐藤花子');
        $response->assertSee('sato@example.com');
    }

    public function test_selected_user_attendance_is_displayed()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => '山田太郎',
            'is_admin' => false,
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-08');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_previous_month_attendance_is_displayed()
    {
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $user = \App\Models\User::factory()->create([
            'is_admin' => false,
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-07-15',
            'clock_in' => '2026-07-15 09:00:00',
            'clock_out' => '2026-07-15 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-07');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_next_month_attendance_is_displayed()
    {
        $admin = \App\Models\User::factory()->create([
            'is_admin' => true,
        ]);

        $user = \App\Models\User::factory()->create([
            'is_admin' => false,
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-09-15',
            'clock_in' => '2026-09-15 10:00:00',
            'clock_out' => '2026-09-15 19:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $user->id . '?month=2026-09');

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    public function test_detail_link_displays_selected_attendance_detail()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/' . $attendance->id);

        $response->assertStatus(200);
    }
}
