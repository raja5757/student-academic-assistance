<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student Academic Assistance') — SAA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --sidebar-w: 260px;
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #3730a3;
            --surface: #f8fafc;
            --card: #ffffff;
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
        }
        * { font-family: 'Inter', system-ui, sans-serif; }
        body { background: var(--surface); color: var(--text); min-height: 100vh; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 50%, #3730a3 100%);
            color: #fff;
            overflow-y: auto;
            z-index: 1040;
            transition: transform .3s;
        }
        .sidebar .brand {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,.12);
        }
        .sidebar .brand h5 { font-weight: 700; font-size: .95rem; margin: 0; letter-spacing: .3px; }
        .sidebar .brand small { font-size: .72rem; color: rgba(255,255,255,.55); }
        .sidebar .nav-link {
            color: rgba(255,255,255,.72);
            padding: .65rem 1.25rem;
            font-size: .84rem;
            font-weight: 500;
            border-radius: 0;
            display: flex; align-items: center; gap: .6rem;
            transition: all .2s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .sidebar .nav-link.active { border-left: 3px solid var(--primary-light); }
        .sidebar .nav-link i { font-size: 1.05rem; width: 20px; text-align: center; }
        .sidebar .nav-section {
            padding: .6rem 1.25rem .3rem;
            font-size: .68rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,.38);
            font-weight: 600;
        }

        /* Main */
        .main-content { margin-left: var(--sidebar-w); min-height: 100vh; }
        .top-nav {
            height: 64px;
            background: var(--card);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky; top: 0; z-index: 1030;
        }
        .page-content { padding: 1.5rem; }

        /* Cards */
        .stat-card {
            background: var(--card);
            border-radius: 14px;
            padding: 1.4rem;
            border: 1px solid var(--border);
            transition: box-shadow .25s, transform .25s;
        }
        .stat-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,.06); transform: translateY(-2px); }
        .stat-card .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
        }
        .stat-card .stat-value { font-size: 1.6rem; font-weight: 700; }
        .stat-card .stat-label { font-size: .78rem; color: var(--text-muted); font-weight: 500; }

        /* Risk badges */
        .risk-low { background: #d1fae5; color: #065f46; }
        .risk-medium { background: #fef3c7; color: #92400e; }
        .risk-high { background: #fee2e2; color: #991b1b; }

        /* Content card */
        .content-card {
            background: var(--card);
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
        }
        .content-card .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 1rem 1.25rem;
            font-weight: 600;
            font-size: .92rem;
        }
        .content-card .card-body { padding: 1.25rem; }

        /* Tables */
        .table th { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted); }
        .table td { vertical-align: middle; font-size: .88rem; }

        /* Badge */
        .badge-status { padding: .35em .7em; font-size: .73rem; font-weight: 600; border-radius: 6px; }

        /* Chat */
        .chat-container { max-height: 460px; overflow-y: auto; }
        .chat-bubble { max-width: 80%; padding: .75rem 1rem; border-radius: 16px; font-size: .88rem; line-height: 1.55; }
        .chat-user { background: var(--primary); color: #fff; border-bottom-right-radius: 4px; margin-left: auto; }
        .chat-ai { background: #f1f5f9; color: var(--text); border-bottom-left-radius: 4px; }

        /* Progress */
        .progress { height: 8px; border-radius: 4px; background: #e2e8f0; }
        .progress-bar { border-radius: 4px; }

        /* Mobile */
        .sidebar-toggle { display: none; }
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .sidebar-toggle { display: block; }
        }

        /* Toast */
        .toast-container { position: fixed; top: 80px; right: 1.5rem; z-index: 9999; }

        /* Animations */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeInUp .4s ease-out; }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Sidebar --}}
    <nav class="sidebar" id="sidebar">
        <div class="brand">
            <h5><i class="bi bi-mortarboard-fill"></i> SAA Platform</h5>
            <small>Academic Assistance System</small>
        </div>
        <div class="py-2">
            @php $role = auth()->user()?->role?->slug; @endphp

            @if($role === 'student')
                <div class="nav-section">Main</div>
                <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('student.academics') }}" class="nav-link {{ request()->routeIs('student.academics') ? 'active' : '' }}">
                    <i class="bi bi-journal-bookmark"></i> Academics
                </a>
                <div class="nav-section">AI Features</div>
                <a href="{{ route('ai.assistant') }}" class="nav-link {{ request()->routeIs('ai.assistant*') ? 'active' : '' }}">
                    <i class="bi bi-robot"></i> AI Assistant
                </a>
                <a href="{{ route('study-plans.index') }}" class="nav-link {{ request()->routeIs('study-plans*') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i> Study Planner
                </a>
                <a href="{{ route('quizzes.index') }}" class="nav-link {{ request()->routeIs('quizzes*') ? 'active' : '' }}">
                    <i class="bi bi-question-diamond"></i> AI Quizzes
                </a>
                <div class="nav-section">Account</div>
                <a href="{{ route('student.profile') }}" class="nav-link {{ request()->routeIs('student.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i> Profile
                </a>

            @elseif($role === 'teacher')
                <div class="nav-section">Main</div>
                <a href="{{ route('teacher.dashboard') }}" class="nav-link {{ request()->routeIs('teacher.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('teacher.courses') }}" class="nav-link {{ request()->routeIs('teacher.courses*') ? 'active' : '' }}">
                    <i class="bi bi-book"></i> My Courses
                </a>
                <div class="nav-section">Account</div>
                <a href="{{ route('teacher.profile') }}" class="nav-link {{ request()->routeIs('teacher.profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i> Profile
                </a>

            @elseif($role === 'admin')
                <div class="nav-section">Main</div>
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Users
                </a>
                <a href="{{ route('admin.students') }}" class="nav-link {{ request()->routeIs('admin.students*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i> Students
                </a>
                <a href="{{ route('admin.departments') }}" class="nav-link {{ request()->routeIs('admin.departments') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> Departments
                </a>
                <a href="{{ route('admin.subjects') }}" class="nav-link {{ request()->routeIs('admin.subjects') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Subjects
                </a>
                <a href="{{ route('admin.activity-logs') }}" class="nav-link {{ request()->routeIs('admin.activity-logs') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Activity Logs
                </a>
            @endif
        </div>
    </nav>

    {{-- Main Content --}}
    <div class="main-content">
        <header class="top-nav">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-outline-secondary sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="bi bi-list"></i>
                </button>
                <h6 class="mb-0 fw-semibold">@yield('page-title', 'Dashboard')</h6>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted" style="font-size:.82rem">{{ auth()->user()?->name }}</span>
                <span class="badge bg-primary-subtle text-primary rounded-pill" style="font-size:.72rem">{{ ucfirst($role ?? 'guest') }}</span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </header>

        <div class="page-content">
            {{-- Flash messages --}}
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show animate-in" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show animate-in" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
