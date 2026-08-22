<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
            'attendance_id' => $this->attendance_id,
            'user_id' => $this->user_id,
            'clock_in' => $this->clock_in,
            'clock_out' => $this->clock_out,
            'comment' => $this->remark,
            'approval_status' => $this->approval_status,
        ];
    }
}
