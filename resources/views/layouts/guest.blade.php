<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SAA Platform')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        body { min-height: 100vh; display: flex; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4f46e5 100%); margin: 0; }
        .auth-container { display: flex; width: 100%; min-height: 100vh; }
        .auth-left {
            flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center;
            color: #fff; padding: 3rem; position: relative; overflow: hidden;
        }
        .auth-left::before {
            content: ''; position: absolute; width: 400px; height: 400px; border-radius: 50%;
            background: rgba(255,255,255,.04); top: -100px; left: -100px;
        }
        .auth-left::after {
            content: ''; position: absolute; width: 300px; height: 300px; border-radius: 50%;
            background: rgba(255,255,255,.03); bottom: -50px; right: -80px;
        }
        .auth-left h1 { font-size: 2.2rem; font-weight: 700; position: relative; z-index: 1; margin-bottom: 0.5rem; }
        .auth-left p { font-size: .95rem; color: rgba(255,255,255,.7); max-width: 400px; text-align: center; position: relative; z-index: 1; }
        .auth-right {
            flex: 1; display: flex; align-items: center; justify-content: center;
            background: #f8fafc; padding: 2rem;
        }
        .auth-card {
            width: 100%; max-width: 420px;
            background: #fff; border-radius: 18px;
            box-shadow: 0 20px 60px rgba(0,0,0,.08);
            padding: 2.5rem;
        }
        .auth-card h2, .auth-card h3 { font-weight: 700; font-size: 1.4rem; color: #1e1b4b; }
        .form-control { border-radius: 10px; padding: .7rem 1rem; border: 1.5px solid #e2e8f0; font-size: .88rem; }
        .form-control:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,.12); }
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5, #6366f1); border: none;
            border-radius: 10px; padding: .7rem; font-weight: 600; font-size: .9rem;
            transition: transform .2s, box-shadow .2s;
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(79,70,229,.3); background: linear-gradient(135deg, #4338ca, #4f46e5); }
        .form-label { font-size: .82rem; font-weight: 600; color: #475569; }
        @media (max-width: 768px) { .auth-left { display: none; } }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <i class="bi bi-mortarboard-fill" style="font-size: 4rem; margin-bottom: 1.5rem; position: relative; z-index: 1;"></i>
            <h1>SAA Platform</h1>
            <p>Student Academic Assistance & Early Intervention System</p>
            <div class="mt-4 d-flex gap-3" style="position: relative; z-index: 1;">
                <div class="text-center">
                    <div style="font-size: 1.4rem; font-weight: 700;"><i class="bi bi-shield-lock"></i></div>
                    <small style="font-size: .72rem; color: rgba(255,255,255,.55);">Secure Access</small>
                </div>
            </div>
        </div>
        <div class="auth-right">
            <div class="auth-card w-100">
                @yield('content')
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
