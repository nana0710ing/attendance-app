<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'date' => $this->work_date,
            'clock_in' => $this->clock_in
                ? \Carbon\Carbon::parse($this->clock_in)->format('H:i:s')
                : null,
            'clock_out' => $this->clock_out
                ? \Carbon\Carbon::parse($this->clock_out)->format('H:i:s')
                : null,
            'total_time' => $this->total_time,
            'total_break_time' => $this->total_break_time,
            'comment' => $this->remark,
            'breaks' => $this->when(
                $request->route('attendance_record') !== null,
                fn () => AttendanceBreakResource::collection($this->breakTimes)
            ),
            'applications' => $this->when(
                $request->route('attendance_record') !== null,
                fn () => ApplicationResource::collection($this->stampCorrectionRequests)
            ),
        ];
    }
}
