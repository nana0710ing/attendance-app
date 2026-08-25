<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_date_is_displayed()
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 25, 10, 0, 0));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2026年8月25日');
        $response->assertSee('火');
    }
}