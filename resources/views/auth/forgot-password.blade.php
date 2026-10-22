@extends('layouts.guest')

@section('title', 'Forgot Password - SAA Platform')

@section('content')
<h3 class="mb-1">Reset Password</h3>
<p class="text-muted mb-4" style="font-size: .85rem;">Enter your email address to receive a 6-digit reset code.</p>

@if(session('status'))
    <div class="alert alert-success py-2" style="font-size: .84rem;">
        {{ session('status') }}
    </div>
@endif
                
@if ($errors->any())
    <div class="alert alert-danger py-2" style="font-size: .84rem;">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('password.email') }}">
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label">Email Address</label>
        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus>
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
        Send Reset Code
    </button>
</form>

<div class="text-center mt-3">
    <a href="{{ route('login') }}" class="text-decoration-none" style="font-size: .84rem; font-weight: 600;">Back to Login</a>
</div>
@endsection
