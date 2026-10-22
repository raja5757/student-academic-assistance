@extends('layouts.guest')

@section('title', 'Verify Email - SAA Platform')

@section('content')
<h3 class="mb-1">Verify Email</h3>
<p class="text-muted mb-4" style="font-size: .85rem;">Enter the 6-digit code sent to <strong>{{ auth()->user()->email }}</strong>.</p>

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

<form method="POST" action="{{ route('verification.verify') }}">
    @csrf

    <div class="mb-4">
        <label for="otp" class="form-label">Verification Code</label>
        <input id="otp" type="text" class="form-control @error('otp') is-invalid @enderror" name="otp" required autofocus placeholder="123456" maxlength="6">
    </div>

    <button type="submit" class="btn btn-primary w-100 mb-3">
        Verify Account
    </button>
</form>

<div class="text-center mt-3">
    <p class="text-muted mb-2" style="font-size: .84rem;">Code expires in 10 minutes.</p>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-link text-decoration-none p-0" style="font-size: .84rem; font-weight: 600;">Resend Verification Code</button>
    </form>
</div>
@endsection
