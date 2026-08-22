<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Attendance;

class UpdateAttendanceRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $attendanceId = $this->route('attendance_record');
        $attendance = Attendance::find($attendanceId);

        return [
            'date' => [
            'sometimes',
            'date_format:Y-m-d',
            Rule::unique('attendances', 'work_date')
                ->where(fn ($query) => $query->where('user_id', $attendance?->user_id))
                ->ignore($attendanceId),
            ],
            'clock_in' => ['sometimes', 'date_format:H:i:s'],
            'clock_out' => ['nullable', 'date_format:H:i:s', 'after:clock_in'],
            'comment' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.date_format' => '勤怠日はYYYY-MM-DD形式で指定してください。',
            'date.unique' => 'この日付の勤怠は既に登録されています。',
            'clock_in.date_format' => '出勤時刻はHH:MM:SS形式で指定してください。',
            'clock_out.date_format' => '退勤時刻はHH:MM:SS形式で指定してください。',
            'clock_out.after' => '退勤時刻は出勤時刻より後の時刻を指定してください。',
            'comment.string' => '備考は文字列で入力してください。',
            'comment.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
