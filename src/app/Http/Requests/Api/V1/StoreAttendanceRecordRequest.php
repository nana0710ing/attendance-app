<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRecordRequest extends FormRequest
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
        return [
            'user_id' => ['required', 'exists:users,id'],
            'work_date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('attendances', 'work_date')
                    ->where(fn ($query) => $query->where('user_id', $this->user_id)),
            ],
            'clock_in' => ['required', 'date_format:H:i:s'],
            'clock_out' => ['nullable', 'date_format:H:i:s', 'after:clock_in'],
            'remark' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'ユーザーIDは必須です。',
            'user_id.exists' => '指定されたユーザーは存在しません。',
            'work_date.required' => '勤務日は必須です。',
            'work_date.date_format' => '勤務日はYYYY-MM-DD形式で入力してください。',
            'work_date.unique' => 'この勤務日の勤怠記録はすでに存在します。',
            'clock_in.required' => '出勤時刻は必須です。',
            'clock_in.date_format' => '出勤時刻はHH:MM:SS形式で入力してください。',
            'clock_out.date_format' => '退勤時刻はHH:MM:SS形式で入力してください。',
            'clock_out.after' => '退勤時刻は出勤時刻より後にしてください。',
            'remark.string' => '備考は文字列で入力してください。',
            'remark.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
