@extends('layouts.app')
@section('title', 'Teacher Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="animate-in">
    <div class="content-card mb-4">
        <div class="card-header"><i class="bi bi-person me-1"></i> Profile Details</div>
        <div class="card-body">
            <form action="{{ route('teacher.profile.update') }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $teacher->user->name ?? '') }}" required>
                </div>
                <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $teacher->phone ?? '') }}">
                </div>
                <div class="mb-3">
                    <label>Designation</label>
                    <input type="text" name="designation" class="form-control" value="{{ old('designation', $teacher->designation ?? '') }}">
                </div>
                <div class="mb-3">
                    <label>Bio</label>
                    <textarea name="bio" class="form-control">{{ old('bio', $teacher->bio ?? '') }}</textarea>
                </div>
                <button class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</div>
@endsection
