<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_pending_requests_are_displayed()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
        ]);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-08-21',
            'clock_in' => '2026-08-21 10:00:00',
            'clock_out' => '2026-08-21 19:00:00',
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'clock_in' => '2026-08-20 09:30:00',
            'clock_out' => '2026-08-20 18:30:00',
            'remark' => '承認待ちテスト1',
            'approval_status' => 'pending',
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'clock_in' => '2026-08-21 10:30:00',
            'clock_out' => '2026-08-21 19:30:00',
            'remark' => '承認待ちテスト2',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/requests');

        $response->assertStatus(200);
        $response->assertSee('承認待ちテスト1');
        $response->assertSee('承認待ちテスト2');
    }

    public function test_all_approved_requests_are_displayed()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user1 = User::factory()->create([
            'name' => '山田太郎',
        ]);

        $user2 = User::factory()->create([
            'name' => '佐藤花子',
        ]);

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2026-08-20',
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-08-21',
            'clock_in' => '2026-08-21 10:00:00',
            'clock_out' => '2026-08-21 19:00:00',
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user1->id,
            'clock_in' => '2026-08-20 09:30:00',
            'clock_out' => '2026-08-20 18:30:00',
            'remark' => '承認済みテスト1',
            'approval_status' => 'approved',
        ]);

        StampCorrectionRequest::create([
            'attendance_id' => $attendance2->id,
            'user_id' => $user2->id,
            'clock_in' => '2026-08-21 10:30:00',
            'clock_out' => '2026-08-21 19:30:00',
            'remark' => '承認済みテスト2',
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/requests?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済みテスト1');
        $response->assertSee('承認済みテスト2');
    }

    public function test_request_detail_is_displayed_correctly()
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
            'clock_in' => '2026-08-20 09:00:00',
            'clock_out' => '2026-08-20 18:00:00',
        ]);

        $request = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => '2026-08-20 09:30:00',
            'clock_out' => '2026-08-20 18:30:00',
            'remark' => '電車遅延のため修正',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/requests/' . $request->id);

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026年08月20日');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertSee('電車遅延のため修正');
        $response->assertSee('承認');
    }

    public function test_admin_can_approve_correction_request()
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
            'remark' => '修正前',
        ]);

        $request = StampCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => '2026-08-20 09:30:00',
            'clock_out' => '2026-08-20 18:30:00',
            'remark' => '承認テスト',
            'approval_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->patch(
                '/stamp_correction_request/approve/' . $request->id
            );

        $response->assertRedirect();

        $request->refresh();
        $attendance->refresh();

        $this->assertEquals('approved', $request->approval_status);

        $this->assertEquals(
            '2026-08-20 09:30:00',
            \Carbon\Carbon::parse($attendance->clock_in)->format('Y-m-d H:i:s')
        );

        $this->assertEquals(
            '2026-08-20 18:30:00',
            \Carbon\Carbon::parse($attendance->clock_out)->format('Y-m-d H:i:s')
        );

        $this->assertEquals('承認テスト', $attendance->remark);
    }
}