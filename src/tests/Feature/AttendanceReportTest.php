<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_attendance_report(): void
    {
        $response = $this->get('/attendance/report');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_statistics_are_calculated_correctly(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => now()->format('Y-m-d') . ' 09:00:00',
            'clock_out' => now()->format('Y-m-d') . ' 18:00:00',
            'remark' => 'テスト',
        ]);

        $response = $this->get('/attendance/report');

        $response->assertStatus(200);
        $response->assertViewHas('totalWorkMinutes', 540);
        $response->assertViewHas('totalOvertimeMinutes', 60);
        $response->assertViewHas('averageWorkMinutes', 540);
        $response->assertViewHas('monthlyData');
    }

    public function test_user_without_attendance_records_is_handled_safely(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance/report');

        $response->assertStatus(200);
        $response->assertViewHas('totalWorkMinutes', 0);
        $response->assertViewHas('totalOvertimeMinutes', 0);
        $response->assertViewHas('averageWorkMinutes', 0);
        $response->assertViewHas('attendances', function ($attendances) {
            return $attendances->isEmpty();
        });
    }
}