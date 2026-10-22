@extends('layouts.app')
@section('title', 'Subjects')
@section('page-title', 'Subjects')

@section('content')
<div class="animate-in">
    <div class="content-card">
        <div class="card-header d-flex justify-content-between"><span><i class="bi bi-journal-text me-1"></i> Subjects</span><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal"><i class="bi bi-plus"></i> Add</button></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Department</th><th>Credits</th></tr></thead>
                    <tbody>
                        @foreach($subjects as $s)
                        <tr><td><code>{{ $s->code }}</code></td><td class="fw-semibold">{{ $s->name }}</td><td>{{ $s->department?->name ?? '—' }}</td><td>{{ $s->credit_hours }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addSubjectModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('admin.subjects.store') }}">@csrf
        <div class="modal-header"><h6 class="modal-title">Add Subject</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Department</label><select name="department_id" class="form-select"><option value="">— None —</option>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
            <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Credit Hours</label><input type="number" name="credit_hours" class="form-control" value="3" min="1" max="6" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Create</button></div>
    </form>
</div></div></div>
@endsection
