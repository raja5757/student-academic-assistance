@extends('layouts.app')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="animate-in">
    <div class="content-card mb-4">
        <div class="card-header"><i class="bi bi-person me-1"></i> Profile Details</div>
        <div class="card-body">
            <form action="{{ route('student.profile.update') }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $student->phone ?? '') }}">
                </div>
                <div class="mb-3">
                    <label>Address</label>
                    <textarea name="address" class="form-control">{{ old('address', $student->address ?? '') }}</textarea>
                </div>
                <div class="mb-3">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '') }}">
                </div>
                <button class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</div>
@endsection
