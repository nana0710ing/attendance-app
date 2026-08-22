<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
        'remark',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    public function stampCorrectionRequests(): HasMany
    {
        return $this->hasMany(StampCorrectionRequest::class);
    }

    public function getTotalBreakTimeAttribute(): string
    {
        $breakMinutes = 0;

        foreach ($this->breakTimes as $breakTime) {
            if ($breakTime->break_start && $breakTime->break_end) {
                $breakMinutes += Carbon::parse($breakTime->break_start)
                    ->diffInMinutes(Carbon::parse($breakTime->break_end));
            }
        }

        return sprintf('%02d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60);
    }

    public function getTotalTimeAttribute(): string
    {
        if (!$this->clock_in || !$this->clock_out) {
            return '00:00';
        }

        $workMinutes = Carbon::parse($this->clock_in)
            ->diffInMinutes(Carbon::parse($this->clock_out));

        $breakMinutes = 0;

        foreach ($this->breakTimes as $breakTime) {
            if ($breakTime->break_start && $breakTime->break_end) {
                $breakMinutes += Carbon::parse($breakTime->break_start)
                    ->diffInMinutes(Carbon::parse($breakTime->break_end));
            }
        }

        $totalMinutes = max(0, $workMinutes - $breakMinutes);

        return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    }
}
