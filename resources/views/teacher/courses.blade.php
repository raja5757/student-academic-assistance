@extends('layouts.app')
@section('title', 'My Courses')
@section('page-title', 'My Courses')

@section('content')
<div class="animate-in">
    <div class="row g-3">
        @forelse($courses as $course)
        <div class="col-md-6">
            <div class="content-card h-100">
                <div class="card-header d-flex justify-content-between">
                    <span class="fw-bold">{{ $course->title }}</span>
                    <span class="badge bg-primary-subtle text-primary">{{ $course->code }}</span>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-2" style="font-size:.85rem;">{{ $course->subject?->name }} • {{ $course->students->count() }} students</p>
                    <a href="{{ route('teacher.courses.show', $course) }}" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-arrow-right-circle me-1"></i> Manage Course
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="content-card"><div class="card-body text-center py-5 text-muted">No courses assigned.</div></div>
        </div>
        @endforelse
    </div>
</div>
@endsection
