<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Http\Resources\AttendanceRecordResource;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;

class AttendanceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexAttendanceRecordRequest $request)
    {
        $query = Attendance::with(['user', 'breakTimes']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('work_date', $request->date);
        }

        $perPage = min((int) $request->input('per_page', 20), 100);

        $attendances = $query
            ->orderBy('work_date', 'desc')
            ->paginate($perPage);

        return AttendanceRecordResource::collection($attendances);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRecordRequest $request)
    {
        $validated = $request->validated();

        $attendance = Attendance::create([
            'user_id' => $validated['user_id'],
            'work_date' => $validated['work_date'],
            'clock_in' => $validated['work_date'] . ' ' . $validated['clock_in'],
            'clock_out' => isset($validated['clock_out'])
                ? $validated['work_date'] . ' ' . $validated['clock_out']
                : null,
            'remark' => $validated['remark'] ?? null,
            'status' => '勤務中',
        ]);

        return response()->json($attendance, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $attendance = Attendance::with([
            'user',
            'breakTimes',
            'stampCorrectionRequests'
        ])->findOrFail($id);

        return new AttendanceRecordResource($attendance);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRecordRequest $request, string $id)
    {
        $attendance = Attendance::findOrFail($id);

        $this->authorize('update', $attendance);

        $validated = $request->validated();

        $workDate = $validated['work_date'] ?? $attendance->work_date;

        if (isset($validated['clock_in'])) {
            $validated['clock_in'] = $workDate . ' ' . $validated['clock_in'];
        }

        if (array_key_exists('clock_out', $validated)) {
            $validated['clock_out'] = $validated['clock_out']
                ? $workDate . ' ' . $validated['clock_out']
                : null;
        }

        $attendance->update($validated);

        return response()->json($attendance);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $attendance = Attendance::findOrFail($id);

        $this->authorize('delete', $attendance);

        $attendance->delete();

        return response()->json(null, 204);
    }
}
