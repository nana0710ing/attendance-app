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

        if ($request->filled('month')) {
            $query->whereYear('work_date', substr($request->month, 0, 4))
                ->whereMonth('work_date', substr($request->month, 5, 2));
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
            'user_id' => $request->user()->id,
            'work_date' => $validated['date'],
            'clock_in' => $validated['date'] . ' ' . $validated['clock_in'],
            'clock_out' => isset($validated['clock_out'])
                ? $validated['date'] . ' ' . $validated['clock_out']
                : null,
            'remark' => $validated['comment'] ?? null,
            'status' => '勤務中',
        ]);

        $attendance->load([
            'user',
            'breakTimes',
            'stampCorrectionRequests',
        ]);

        return (new AttendanceRecordResource($attendance))
            ->response()
            ->setStatusCode(201);
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

        $workDate = $validated['date'] ?? $attendance->work_date;

        if (isset($validated['clock_in'])) {
            $validated['clock_in'] = $workDate . ' ' . $validated['clock_in'];
        }

        if (array_key_exists('clock_out', $validated)) {
            $validated['clock_out'] = $validated['clock_out']
            ? $workDate . ' ' . $validated['clock_out']
            : null;
        }

        $updateData = [];

        if (array_key_exists('date', $validated)) {
        $updateData['work_date'] = $validated['date'];
        }

        if (array_key_exists('clock_in', $validated)) {
        $updateData['clock_in'] = $validated['clock_in'];
        }

        if (array_key_exists('clock_out', $validated)) {
        $updateData['clock_out'] = $validated['clock_out'];
        }

        if (array_key_exists('comment', $validated)) {
        $updateData['remark'] = $validated['comment'];
        }

        $attendance->update($updateData);

        $attendance->load([
            'user',
            'breakTimes',
            'stampCorrectionRequests',
        ]);

            return new AttendanceRecordResource($attendance);
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
