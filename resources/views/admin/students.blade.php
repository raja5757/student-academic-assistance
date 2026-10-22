@extends('layouts.app')
@section('title', 'Students')
@section('page-title', 'All Students')

@section('content')
<div class="animate-in">
    <div class="content-card">
        <div class="card-header"><i class="bi bi-person-badge me-1"></i> Students ({{ count($students) }})</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Roll No</th><th>Department</th><th>Attendance</th><th>Average</th><th>Risk</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach($students as $s)
                        <tr>
                            <td class="fw-semibold">{{ $s['model']->user->name }}</td>
                            <td><code>{{ $s['model']->roll_no }}</code></td>
                            <td>{{ $s['model']->department?->name }}</td>
                            <td>{{ $s['attendance'] }}%</td>
                            <td>{{ $s['average'] }}%</td>
                            <td><span class="badge badge-status risk-{{ $s['risk']->level }}">{{ strtoupper($s['risk']->level) }}</span></td>
                            <td><a href="{{ route('admin.students.show', $s['model']) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
