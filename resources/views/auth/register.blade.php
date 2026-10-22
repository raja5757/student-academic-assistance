<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — SAA Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        body { min-height: 100vh; display: flex; background: #f8fafc; }
        .auth-container { display: flex; width: 100%; min-height: 100vh; }
        .auth-left {
            flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;
            background: linear-gradient(135deg, #1e1b4b, #312e81, #4f46e5); color: #fff; padding: 3rem;
        }
        .auth-left h1 { font-size: 2rem; font-weight: 700; }
        .auth-left p { color: rgba(255,255,255,.7); max-width: 380px; text-align: center; font-size: .9rem; }
        .auth-right { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .auth-card { width: 100%; max-width: 480px; background: #fff; border-radius: 18px; box-shadow: 0 20px 60px rgba(0,0,0,.08); padding: 2.5rem; }
        .auth-card h3 { font-weight: 700; font-size: 1.3rem; }
        .form-control { border-radius: 10px; padding: .65rem 1rem; border: 1.5px solid #e2e8f0; font-size: .85rem; }
        .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
        .form-select { border-radius: 10px; padding: .65rem 1rem; border: 1.5px solid #e2e8f0; font-size: .85rem; }
        .btn-primary { background: linear-gradient(135deg, #4f46e5, #6366f1); border: none; border-radius: 10px; padding: .65rem; font-weight: 600; font-size: .88rem; }
        .btn-primary:hover { background: linear-gradient(135deg, #4338ca, #4f46e5); }
        .form-label { font-size: .8rem; font-weight: 600; color: #475569; }
        @media (max-width: 768px) { .auth-left { display: none; } }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <i class="bi bi-mortarboard-fill" style="font-size: 3.5rem; margin-bottom: 1.2rem;"></i>
            <h1>Join SAA</h1>
            <p>Create your student account and access AI-powered academic assistance, study planning, and early intervention tools.</p>
        </div>
        <div class="auth-right">
            <div class="auth-card">
                <h3 class="mb-1">Create Account</h3>
                <p class="text-muted mb-4" style="font-size: .83rem;">Register as a new student</p>

                @if($errors->any())
                    <div class="alert alert-danger py-2" style="font-size: .82rem;">
                        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif

                <a href="{{ route('auth.google') }}" class="btn btn-outline-dark w-100 mb-3 d-flex align-items-center justify-content-center" style="border-radius: 10px; font-weight: 500;">
                    <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" width="20" class="me-2">
                    Continue with Google
                </a>

                <div class="d-flex align-items-center mb-3">
                    <hr class="flex-grow-1">
                    <span class="mx-2 text-muted" style="font-size: .8rem;">OR</span>
                    <hr class="flex-grow-1">
                </div>

                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter your full name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="you@university.edu" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Roll Number</label>
                            <input type="text" name="roll_no" class="form-control" value="{{ old('roll_no') }}" placeholder="BSCS-21-001" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" class="form-select">
                                @foreach($semesters as $sem)
                                    <option value="{{ $sem->id }}" {{ old('semester_id') == $sem->id ? 'selected' : '' }}>{{ $sem->name }} {{ $sem->year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Program</label>
                            <input type="text" name="program" class="form-control" value="{{ old('program', 'BS Computer Science') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-person-plus me-1"></i> Create Account
                            </button>
                        </div>
                    </div>
                </form>
                <p class="text-center mt-3 mb-0" style="font-size: .83rem;">
                    Already registered? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
