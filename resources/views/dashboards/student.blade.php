@extends('layouts.app')
@section('title', 'Student Dashboard')
@section('page-title', 'Student Dashboard')

@section('content')
<div class="animate-in">
    {{-- Stats Row --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-book"></i></div>
                    <div>
                        <div class="stat-value">{{ $data['totalCourses'] }}</div>
                        <div class="stat-label">Courses</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-check2-circle"></i></div>
                    <div>
                        <div class="stat-value">{{ $data['attendancePercentage'] }}%</div>
                        <div class="stat-label">Attendance</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-bar-chart-line"></i></div>
                    <div>
                        <div class="stat-value">{{ $data['averageMarks'] }}%</div>
                        <div class="stat-label">Average Marks</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon risk-{{ $data['risk']->level }}"><i class="bi bi-exclamation-triangle"></i></div>
                    <div>
                        <div class="stat-value">{{ $data['risk']->score }}<small style="font-size:.6rem">/100</small></div>
                        <div class="stat-label">Risk: {{ strtoupper($data['risk']->level) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance & Study Progress --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="stat-card text-center">
                <div class="stat-label mb-2">Overall Performance</div>
                <div class="stat-value" style="font-size:2.2rem; color: var(--primary);">{{ $data['performanceScore'] }}%</div>
                <div class="progress mt-2">
                    <div class="progress-bar bg-primary" style="width: {{ $data['performanceScore'] }}%"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="stat-card text-center">
                <div class="stat-label mb-2">Study Plan Progress</div>
                <div class="stat-value" style="font-size:2.2rem; color: var(--success);">{{ $data['studyProgress'] }}%</div>
                <div class="progress mt-2">
                    <div class="progress-bar bg-success" style="width: {{ $data['studyProgress'] }}%"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="stat-card">
                <div class="stat-label mb-2">Quick Actions</div>
                <div class="d-grid gap-2">
                    <a href="{{ route('ai.assistant') }}" class="btn btn-sm btn-primary"><i class="bi bi-robot me-1"></i> AI Assistant</a>
                    <a href="{{ route('study-plans.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-calendar3 me-1"></i> Study Planner</a>
                    <a href="{{ route('quizzes.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-question-diamond me-1"></i> AI Quiz</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-bar-chart me-1"></i> Subject Performance</div>
                <div class="card-body">
                    <canvas id="subjectChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-graph-up me-1"></i> Marks Trend</div>
                <div class="card-body">
                    <canvas id="marksChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Risk & Recommendations Row --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-exclamation-triangle me-1"></i> Academic Risk Analysis</div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="badge badge-status risk-{{ $data['risk']->level }}" style="font-size:.85rem; padding:.5em 1em;">
                            {{ strtoupper($data['risk']->level) }} RISK
                        </span>
                        <span class="fw-bold" style="font-size:1.4rem;">Score: {{ $data['risk']->score }}/100</span>
                    </div>
                    <p class="text-muted mb-2" style="font-size:.82rem;"><em>This is an academic early-warning indicator, not a definitive prediction.</em></p>
                    <ul class="list-unstyled mb-0">
                        @foreach($data['risk']->reasons ?? [] as $reason)
                            <li class="mb-1"><i class="bi bi-info-circle text-warning me-1"></i> <span style="font-size:.85rem;">{{ $reason }}</span></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-lightbulb me-1"></i> AI Recommendations</div>
                <div class="card-body">
                    @forelse($data['recommendations'] as $rec)
                        <div class="d-flex gap-2 mb-3 pb-3 border-bottom">
                            <span class="badge badge-status risk-{{ $rec->priority === 'high' ? 'high' : ($rec->priority === 'medium' ? 'medium' : 'low') }}" style="height:fit-content;">{{ $rec->priority }}</span>
                            <div>
                                <div class="fw-semibold" style="font-size:.88rem;">{{ $rec->title }}</div>
                                <div class="text-muted" style="font-size:.82rem;">{{ $rec->body }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0" style="font-size:.85rem;">No recommendations yet. Use the AI Study Planner to generate personalized advice.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Weak Subjects & Upcoming --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-graph-down me-1"></i> Weak Subjects</div>
                <div class="card-body">
                    @forelse($data['weakSubjects'] as $sub)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:.85rem;">{{ $sub['name'] }}</span>
                            <span class="badge bg-danger-subtle text-danger">{{ $sub['average'] }}%</span>
                        </div>
                        <div class="progress mb-3">
                            <div class="progress-bar bg-danger" style="width: {{ $sub['average'] }}%"></div>
                        </div>
                    @empty
                        <p class="text-muted mb-0" style="font-size:.85rem;">All subjects above 60%. Keep it up!</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-journal-text me-1"></i> Upcoming Assignments</div>
                <div class="card-body">
                    @forelse($data['upcomingAssignments'] as $a)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <div class="fw-semibold" style="font-size:.84rem;">{{ $a->title }}</div>
                                <small class="text-muted">{{ $a->subject?->name }}</small>
                            </div>
                            <span class="badge bg-warning-subtle text-warning">{{ $a->due_date?->format('M d') }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0" style="font-size:.85rem;">No upcoming assignments.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-calendar-event me-1"></i> Upcoming Exams</div>
                <div class="card-body">
                    @forelse($data['upcomingExams'] as $e)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <div>
                                <div class="fw-semibold" style="font-size:.84rem;">{{ $e->title }}</div>
                                <small class="text-muted">{{ $e->subject?->name }}</small>
                            </div>
                            <span class="badge bg-danger-subtle text-danger">{{ $e->exam_date?->format('M d') }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0" style="font-size:.85rem;">No upcoming exams.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Attendance Chart --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-pie-chart me-1"></i> Attendance by Subject</div>
                <div class="card-body">
                    <canvas id="attendanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-bell me-1"></i> Notifications</div>
                <div class="card-body">
                    @forelse($data['notifications'] as $n)
                        <div class="d-flex gap-2 mb-2 pb-2 border-bottom">
                            <i class="bi bi-{{ $n->type === 'risk' ? 'exclamation-triangle text-danger' : 'info-circle text-primary' }}"></i>
                            <div>
                                <div class="fw-semibold" style="font-size:.84rem;">{{ $n->title }}</div>
                                <small class="text-muted">{{ $n->body }}</small>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0" style="font-size:.85rem;">No notifications.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Subject Performance Bar Chart
const subjectData = @json($data['subjectPerformance']);
new Chart(document.getElementById('subjectChart'), {
    type: 'bar',
    data: {
        labels: subjectData.map(s => s.name),
        datasets: [{
            label: 'Average %',
            data: subjectData.map(s => s.average),
            backgroundColor: subjectData.map(s => s.average >= 75 ? '#10b981' : s.average >= 60 ? '#f59e0b' : '#ef4444'),
            borderRadius: 6
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100 } } }
});

// Marks Trend Line Chart
const marksData = @json($data['marksTrend']);
new Chart(document.getElementById('marksChart'), {
    type: 'line',
    data: {
        labels: marksData.map(m => m.label),
        datasets: [{
            label: 'Score %',
            data: marksData.map(m => m.value),
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79,70,229,.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: '#4f46e5'
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } }
});

// Attendance Doughnut Chart
const attData = @json($data['attendanceChart']);
new Chart(document.getElementById('attendanceChart'), {
    type: 'doughnut',
    data: {
        labels: attData.map(a => a.label),
        datasets: [{
            data: attData.map(a => a.value),
            backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#06b6d4', '#8b5cf6']
        }]
    },
    options: { responsive: true, cutout: '55%' }
});
</script>
@endpush
