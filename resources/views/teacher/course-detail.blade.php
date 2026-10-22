@extends('layouts.app')
@section('title', $course->title)
@section('page-title', $course->title)

@section('content')
<div class="animate-in">
    {{-- Course Info --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value">{{ $students->count() }}</div><div class="stat-label">Students</div></div></div>
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value">{{ $students->where('risk.level', 'high')->count() }}</div><div class="stat-label">High Risk</div></div></div>
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value">{{ round($students->avg('attendance')) }}%</div><div class="stat-label">Avg Attendance</div></div></div>
        <div class="col-md-3"><div class="stat-card text-center"><div class="stat-value">{{ round($students->avg('average')) }}%</div><div class="stat-label">Avg Marks</div></div></div>
    </div>

    {{-- Action Buttons --}}
    <div class="d-flex gap-2 mb-4">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#attendanceModal"><i class="bi bi-calendar-check me-1"></i> Record Attendance</button>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#marksModal"><i class="bi bi-bar-chart me-1"></i> Enter Marks</button>
        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#assignmentModal"><i class="bi bi-journal-text me-1"></i> Create Assignment</button>
        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#examModal"><i class="bi bi-calendar-event me-1"></i> Create Exam</button>
    </div>

    {{-- Student Table --}}
    <div class="content-card">
        <div class="card-header"><i class="bi bi-people me-1"></i> Enrolled Students</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Roll No</th><th>Attendance</th><th>Average</th><th>Risk</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach($students as $s)
                        <tr>
                            <td class="fw-semibold">{{ $s['model']->user->name }}</td>
                            <td><code>{{ $s['model']->roll_no }}</code></td>
                            <td>{{ $s['attendance'] }}%</td>
                            <td>{{ $s['average'] }}%</td>
                            <td><span class="badge badge-status risk-{{ $s['risk']->level }}">{{ strtoupper($s['risk']->level) }}</span></td>
                            <td><a href="{{ route('teacher.students.show', $s['model']) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Attendance Modal --}}
<div class="modal fade" id="attendanceModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('teacher.attendance.store') }}">@csrf
        <div class="modal-header"><h6 class="modal-title">Record Attendance</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="subject_id" value="{{ $course->subject_id }}">
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <div class="mb-3"><label class="form-label">Student</label><select name="student_id" class="form-select" required>@foreach($students as $s)<option value="{{ $s['model']->id }}">{{ $s['model']->user->name }} ({{ $s['model']->roll_no }})</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Date</label><input type="date" name="class_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
            <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select" required><option value="present">Present</option><option value="absent">Absent</option><option value="late">Late</option><option value="excused">Excused</option></select></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Marks Modal --}}
<div class="modal fade" id="marksModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('teacher.marks.store') }}">@csrf
        <div class="modal-header"><h6 class="modal-title">Enter Marks</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="subject_id" value="{{ $course->subject_id }}">
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <div class="mb-3"><label class="form-label">Student</label><select name="student_id" class="form-select" required>@foreach($students as $s)<option value="{{ $s['model']->id }}">{{ $s['model']->user->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Type</label><select name="type" class="form-select" required>@foreach(['assignment','quiz','midterm','final','project'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="row"><div class="col"><label class="form-label">Obtained</label><input type="number" name="obtained_marks" class="form-control" step="0.01" min="0" required></div><div class="col"><label class="form-label">Total</label><input type="number" name="total_marks" class="form-control" value="100" min="1" required></div></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Assignment Modal --}}
<div class="modal fade" id="assignmentModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('teacher.assignments.store') }}">@csrf
        <div class="modal-header"><h6 class="modal-title">Create Assignment</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="subject_id" value="{{ $course->subject_id }}">
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            <div class="row"><div class="col"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control" required></div><div class="col"><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></div></div>
            <div class="mb-3 mt-3"><label class="form-label">Total Marks</label><input type="number" name="total_marks" class="form-control" value="20" min="1" required></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Create</button></div>
    </form>
</div></div></div>

{{-- Exam Modal --}}
<div class="modal fade" id="examModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('teacher.exams.store') }}">@csrf
        <div class="modal-header"><h6 class="modal-title">Create Exam</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <input type="hidden" name="subject_id" value="{{ $course->subject_id }}">
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Type</label><input type="text" name="type" class="form-control" value="exam" required></div>
            <div class="row"><div class="col"><label class="form-label">Date</label><input type="date" name="exam_date" class="form-control" required></div><div class="col"><label class="form-label">Time</label><input type="time" name="exam_time" class="form-control"></div></div>
            <div class="mb-3 mt-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Total Marks</label><input type="number" name="total_marks" class="form-control" value="100" min="1" required></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Create</button></div>
    </form>
</div></div></div>
@endsection
