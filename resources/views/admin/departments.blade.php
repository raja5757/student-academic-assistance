@extends('layouts.app')
@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
<div class="animate-in">
    <div class="content-card mb-4">
        <div class="card-header d-flex justify-content-between"><span><i class="bi bi-building me-1"></i> Departments</span><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal"><i class="bi bi-plus"></i> Add</button></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Code</th><th>Subjects</th><th>Description</th></tr></thead>
                    <tbody>
                        @foreach($departments as $dept)
                        <tr>
                            <td class="fw-semibold">{{ $dept->name }}</td>
                            <td><code>{{ $dept->code }}</code></td>
                            <td>{{ $dept->subjects_count }}</td>
                            <td><small class="text-muted">{{ Str::limit($dept->description, 60) }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addDeptModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('admin.departments.store') }}">@csrf
        <div class="modal-header"><h6 class="modal-title">Add Department</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Code</label><input type="text" name="code" class="form-control" maxlength="10" required></div>
            <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        </div>
        <div class="modal-footer"><button class="btn btn-primary">Create</button></div>
    </form>
</div></div></div>
@endsection
