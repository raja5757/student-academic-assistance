@extends('layouts.guest')

@section('title', 'Reset Password - SAA Platform')

@section('content')
<h3 class="mb-1">New Password</h3>
<p class="text-muted mb-4" style="font-size: .85rem;">Enter the 6-digit code sent to your email and your new password.</p>

@if(session('status'))
    <div class="alert alert-success py-2" style="font-size: .84rem;">
        {{ session('status') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger py-2" style="font-size: .84rem;">
        {{ session('error') }}
    </div>
@endif
                
@if ($errors->any())
    <div class="alert alert-danger py-2" style="font-size: .84rem;">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="email" value="{{ request('email') }}">

    <div class="mb-3">
        <label for="otp" class="form-label">6-Digit Reset Code</label>
        <input id="otp" type="text" class="form-control @error('otp') is-invalid @enderror" name="otp" required autofocus placeholder="123456" maxlength="6">
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">New Password</label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required>
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm New Password</label>
        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
        Reset Password
    </button>
</form>
@endsection
