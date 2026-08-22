<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use App\Models\Attendance;

class AttendanceRecordApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_records_index_can_be_accessed_without_authentication(): void
    {
        $response = $this->getJson('/api/v1/attendance-records');

        $response->assertStatus(200);
    }

    public function test_attendance_record_cannot_be_created_without_authentication(): void
    {
        $response = $this->postJson('/api/v1/attendance-records', [
            'user_id' => 2,
            'work_date' => '2026-09-01',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_own_attendance_record(): void
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/attendance-records', [
            'date' => '2026-08-23',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
            'comment' => 'APIテスト',
        ]);

        $response->assertStatus(201);
    }

    public function test_user_cannot_update_another_users_attendance_record(): void
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $otherUser = User::factory()->create([
            'is_admin' => 0,
        ]);

        $otherAttendance = Attendance::create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-09-03',
            'clock_in' => '2026-09-03 09:00:00',
            'clock_out' => '2026-09-03 17:00:00',
            'status' => '退勤済',
            'remark' => '他ユーザーの勤怠',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson(
            '/api/v1/attendance-records/' . $otherAttendance->id,
            [
                'remark' => '他ユーザー更新テスト',
            ]
        );

        $response->assertStatus(403);
    }

    public function test_user_can_update_own_attendance_record(): void
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-23',
            'clock_in' => '2026-08-23 09:00:00',
            'clock_out' => '2026-08-23 17:00:00',
            'status' => '退勤済',
        ]);

        Sanctum::actingAs($user);

        $response = $this->patchJson(
            "/api/v1/attendance-records/{$attendance->id}",
            [
                'remark' => '本人更新テスト',
            ]
        );

        $response->assertStatus(200);
    }

    public function test_admin_can_update_another_users_attendance_record(): void
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-23',
            'clock_in' => '2026-08-23 09:00:00',
            'clock_out' => '2026-08-23 17:00:00',
            'status' => '退勤済',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson(
            "/api/v1/attendance-records/{$attendance->id}",
            [
                'remark' => '管理者更新テスト',
            ]
        );

        $response->assertStatus(200);
    }

    public function test_user_cannot_delete_another_users_attendance_record(): void
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $otherUser = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-08-23',
            'clock_in' => '2026-08-23 09:00:00',
            'clock_out' => '2026-08-23 17:00:00',
            'status' => '退勤済',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            "/api/v1/attendance-records/{$attendance->id}"
        );

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_another_users_attendance_record(): void
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-23',
            'clock_in' => '2026-08-23 09:00:00',
            'clock_out' => '2026-08-23 17:00:00',
            'status' => '退勤済',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson(
            "/api/v1/attendance-records/{$attendance->id}"
        );

        $response->assertStatus(204);
    }

    public function test_invalid_work_date_returns_validation_error(): void
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/attendance-records', [
            'user_id' => $user->id,
            'work_date' => 'invalid-date',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date']);
    }

    public function test_duplicate_work_date_cannot_be_created(): void
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        \App\Models\Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-08-23',
            'clock_in' => '2026-08-23 09:00:00',
            'clock_out' => '2026-08-23 17:00:00',
            'status' => '退勤済',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/attendance-records', [
            'user_id' => $user->id,
            'work_date' => '2026-08-23',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date']);
    }

    public function test_clock_out_must_be_after_clock_in(): void
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/attendance-records', [
            'user_id' => $user->id,
            'work_date' => '2026-08-23',
            'clock_in' => '17:00:00',
            'clock_out' => '09:00:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['clock_out']);
    }

    public function test_can_filter_attendances_by_month(): void
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-10',
            'status' => 'finished',
        ]);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-10',
            'status' => 'finished',
        ]);

        $response = $this->getJson('/api/v1/attendance-records?month=2026-05');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.date', '2026-05-10');
    }
}
