<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceDummySeeder extends Seeder
{
    public function run(): void
    {
        // 一般ユーザー1
        $user1 = User::updateOrCreate(
            ['email' => 'user1@example.com'],
            [
                'name' => 'ユーザー1',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => false,
            ]
        );

        // 一般ユーザー2
        $user2 = User::updateOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => 'ユーザー2',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => false,
            ]
        );

        // 管理者ユーザー3
        $user3 = User::updateOrCreate(
            ['email' => 'user3@example.com'],
            [
                'name' => '管理者',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );

        // user1：過去5ヶ月、各月平日15日
        for ($i = 5; $i >= 1; $i--) {
            $month = now()->subMonths($i)->startOfMonth();
            $created = 0;

            while ($created < 15) {
                if ($month->isWeekday()) {
                    $this->createAttendance(
                        $user1,
                        $month->copy(),
                        '09:00',
                        '18:00'
                    );
                    $created++;
                }

                $month->addDay();
            }
        }

        // user1：当月17日分
        $patterns = [
            ['09:00', '18:00'], // 通常
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],

            ['09:00', '20:00'], // 残業
            ['09:00', '20:00'],
            ['09:00', '20:00'],

            ['09:30', '18:00'], // 遅刻
            ['09:30', '18:00'],

            ['09:00', '17:00'], // 早退

            ['08:00', '21:00'], // 長時間労働
        ];

        $date = now()->startOfMonth();
        $index = 0;

        while ($index < count($patterns)) {
            if ($date->isWeekday()) {
                [$clockIn, $clockOut] = $patterns[$index];

                $this->createAttendance(
                    $user1,
                    $date->copy(),
                    $clockIn,
                    $clockOut
                );

                $index++;
            }

            $date->addDay();
        }

        // user2・user3にも勤怠ダミーデータ
        foreach ([$user2, $user3] as $user) {
            for ($i = 1; $i <= 5; $i++) {
                $date = now()->subDays($i);

                $this->createAttendance(
                    $user,
                    $date,
                    '09:00',
                    '18:00'
                );
            }
        }
    }

    private function createAttendance(
        User $user,
        Carbon $date,
        string $clockIn,
        string $clockOut
    ): void {
        $attendance = Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $date->format('Y-m-d'),
            ],
            [
                'clock_in' => $date->format('Y-m-d') . ' ' . $clockIn . ':00',
                'clock_out' => $date->format('Y-m-d') . ' ' . $clockOut . ':00',
                'status' => '勤務終了',
            ]
        );

        BreakTime::updateOrCreate(
            [
                'attendance_id' => $attendance->id,
                'break_start' => $date->format('Y-m-d') . ' 12:00:00',
            ],
            [
                'break_end' => $date->format('Y-m-d') . ' 13:00:00',
            ]
        );
    }
}