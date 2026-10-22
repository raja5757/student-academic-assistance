@extends('layouts.app')
@section('title', 'Academics')
@section('page-title', 'My Academics')

@section('content')
<div class="animate-in">
    {{-- Enrolled Courses --}}
    <div class="content-card mb-4">
        <div class="card-header"><i class="bi bi-book me-1"></i> Enrolled Courses</div>
        <div class="card-body">
            <div class="row g-3">
                @forelse($student->courses as $course)
                <div class="col-md-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <h6 class="fw-bold mb-1">{{ $course->subject?->name }}</h6>
                        <small class="text-muted">{{ $course->code }}</small><br>
                        <small class="text-muted">{{ $course->teacher?->user?->name ?? 'TBA' }}</small>
                    </div>
                </div>
                @empty
                <p class="text-muted">No courses enrolled yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Marks --}}
    <div class="content-card mb-4">
        <div class="card-header d-flex justify-content-between">
            <span><i class="bi bi-bar-chart me-1"></i> Marks & Performance</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMarkModal"><i class="bi bi-plus"></i> Add Mark</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Subject</th><th>Type</th><th>Title</th><th>Obtained</th><th>Total</th><th>%</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($student->marks->sortByDesc('recorded_on') as $mark)
                        <tr>
                            <td>{{ $mark->subject?->name }}</td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ ucfirst($mark->type) }}</span></td>
                            <td>{{ $mark->title }}</td>
                            <td class="fw-bold">{{ $mark->obtained_marks }}</td>
                            <td>{{ $mark->total_marks }}</td>
                            <td>{{ round(($mark->obtained_marks / max(1, $mark->total_marks)) * 100) }}%</td>
                            <td><small class="text-muted">{{ $mark->recorded_on?->format('M d') }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-muted text-center">No marks recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Attendance --}}
    <div class="content-card mb-4">
        <div class="card-header d-flex justify-content-between">
            <span><i class="bi bi-calendar-check me-1"></i> Attendance Records</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAttendanceModal"><i class="bi bi-plus"></i> Record</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Subject</th><th>Date</th><th>Status</th><th>Remarks</th></tr></thead>
                    <tbody>
                        @forelse($student->attendanceRecords->sortByDesc('class_date')->take(20) as $att)
                        <tr>
                            <td>{{ $att->subject?->name }}</td>
                            <td>{{ $att->class_date?->format('M d, Y') }}</td>
                            <td>
                                <span class="badge {{ $att->status === 'present' ? 'bg-success' : ($att->status === 'absent' ? 'bg-danger' : 'bg-warning') }}">
                                    {{ ucfirst($att->status) }}
                                </span>
                            </td>
                            <td><small class="text-muted">{{ $att->remarks }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted text-center">No attendance records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Assignments --}}
    <div class="content-card mb-4">
        <div class="card-header d-flex justify-content-between">
            <span><i class="bi bi-journal-text me-1"></i> Assignments</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAssignmentModal"><i class="bi bi-plus"></i> Add</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Title</th><th>Subject</th><th>Due</th><th>Priority</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($assignments as $a)
                        <tr>
                            <td class="fw-semibold">{{ $a->title }}</td>
                            <td>{{ $a->subject?->name }}</td>
                            <td>{{ $a->due_date?->format('M d') }}</td>
                            <td><span class="badge {{ $a->priority === 'high' ? 'bg-danger' : ($a->priority === 'medium' ? 'bg-warning' : 'bg-secondary') }}">{{ $a->priority }}</span></td>
                            <td><span class="badge {{ $a->status === 'completed' ? 'bg-success' : ($a->status === 'overdue' ? 'bg-danger' : 'bg-info') }}">{{ str_replace('_',' ',ucfirst($a->status)) }}</span></td>
                            <td>
                                @if($a->student_id === $student->id)
                                <form method="POST" action="{{ route('student.assignments.update', $a) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm" style="width:auto; display:inline;">
                                        @foreach(['pending','in_progress','completed','overdue'] as $st)
                                            <option value="{{ $st }}" {{ $a->status === $st ? 'selected' : '' }}>{{ str_replace('_',' ',ucfirst($st)) }}</option>
                                        @endforeach
                                    </select>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-muted text-center">No assignments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Exams --}}
    <div class="content-card mb-4">
        <div class="card-header d-flex justify-content-between">
            <span><i class="bi bi-calendar-event me-1"></i> Exams</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addExamModal"><i class="bi bi-plus"></i> Add</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Title</th><th>Subject</th><th>Type</th><th>Date</th><th>Time</th><th>Location</th><th>Marks</th></tr></thead>
                    <tbody>
                        @forelse($exams as $e)
                        <tr>
                            <td class="fw-semibold">{{ $e->title }}</td>
                            <td>{{ $e->subject?->name }}</td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ ucfirst($e->type) }}</span></td>
                            <td>{{ $e->exam_date?->format('M d, Y') }}</td>
                            <td>{{ $e->exam_time }}</td>
                            <td>{{ $e->location ?? '—' }}</td>
                            <td>{{ $e->total_marks }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-muted text-center">No exams scheduled.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div class="content-card mb-4">
        <div class="card-header d-flex justify-content-between">
            <span><i class="bi bi-file-earmark-text me-1"></i> Study Documents</span>
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadDocModal"><i class="bi bi-upload"></i> Upload</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>File</th><th>Subject</th><th>Status</th><th>Uploaded</th></tr></thead>
                    <tbody>
                        @forelse($documents as $doc)
                        <tr>
                            <td>{{ $doc->original_name }}</td>
                            <td>{{ $doc->subject?->name ?? '—' }}</td>
                            <td><span class="badge {{ $doc->status === 'processed' ? 'bg-success' : 'bg-warning' }}">{{ $doc->status }}</span></td>
                            <td><small class="text-muted">{{ $doc->created_at->format('M d, Y') }}</small></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-muted text-center">No documents uploaded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}

{{-- Add Mark Modal --}}
<div class="modal fade" id="addMarkModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('student.marks.store') }}">@csrf
        <div class="modal-header"><h6 class="modal-title">Record Mark</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Subject</label><select name="subject_id" class="form-select" required>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Type</label><select name="type" class="form-select" required>@foreach(['assignment','quiz','midterm','final','project'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="row"><div class="col"><label class="form-label">Obtained</label><input type="number" name="obtained_marks" class="form-control" step="0.01" min="0" required></div><div class="col"><label class="form-label">Total</label><input type="number" name="total_marks" class="form-control" value="100" min="1" required></div></div>
            <div class="mb-3 mt-3"><label class="form-label">Date</label><input type="date" name="recorded_on" class="form-control"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save Mark</button></div>
    </form>
</div></div></div>

{{-- Add Attendance Modal --}}
<div class="modal fade" id="addAttendanceModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('student.attendance.store') }}">@csrf
        <div class="modal-header"><h6 class="modal-title">Record Attendance</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Subject</label><select name="subject_id" class="form-select" required>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Date</label><input type="date" name="class_date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
            <div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select" required>@foreach(['present','absent','late','excused'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Remarks</label><input type="text" name="remarks" class="form-control" maxlength="500"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Add Assignment Modal --}}
<div class="modal fade" id="addAssignmentModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('student.assignments.store') }}">@csrf
        <div class="modal-header"><h6 class="modal-title">Add Assignment</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Subject</label><select name="subject_id" class="form-select" required>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            <div class="mb-3"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control" required></div>
            <div class="row"><div class="col"><label class="form-label">Priority</label><select name="priority" class="form-select">@foreach(['low','medium','high'] as $p)<option value="{{ $p }}" {{ $p==='medium'?'selected':'' }}>{{ ucfirst($p) }}</option>@endforeach</select></div><div class="col"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(['pending','in_progress','completed','overdue'] as $s)<option value="{{ $s }}">{{ str_replace('_',' ',ucfirst($s)) }}</option>@endforeach</select></div></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Add Exam Modal --}}
<div class="modal fade" id="addExamModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('student.exams.store') }}">@csrf
        <div class="modal-header"><h6 class="modal-title">Add Exam</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Subject</label><select name="subject_id" class="form-select" required>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Type</label><input type="text" name="type" class="form-control" value="exam" required></div>
            <div class="row"><div class="col"><label class="form-label">Date</label><input type="date" name="exam_date" class="form-control" required></div><div class="col"><label class="form-label">Time</label><input type="time" name="exam_time" class="form-control"></div></div>
            <div class="mb-3 mt-3"><label class="form-label">Location</label><input type="text" name="location" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Total Marks</label><input type="number" name="total_marks" class="form-control" value="100" min="1" required></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
    </form>
</div></div></div>

{{-- Upload Document Modal --}}
<div class="modal fade" id="uploadDocModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('student.documents.upload') }}" enctype="multipart/form-data">@csrf
        <div class="modal-header"><h6 class="modal-title">Upload Study Document</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Subject (optional)</label><select name="subject_id" class="form-select"><option value="">— None —</option>@foreach($subjects as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">File (PDF, TXT, DOCX — max 5MB)</label><input type="file" name="document" class="form-control" accept=".txt,.pdf,.docx" required></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Upload & Process</button></div>
    </form>
</div></div></div>
@endsection
