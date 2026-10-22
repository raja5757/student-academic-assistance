@extends('layouts.app')
@section('title', 'Teacher Dashboard')
@section('page-title', 'Teacher — Early Intervention Dashboard')

@section('content')
<div class="animate-in">
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="stat-card text-center">
                <div class="stat-value">{{ $data['totalStudents'] }}</div>
                <div class="stat-label">Students</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-card text-center">
                <div class="stat-value text-success">{{ $data['lowRisk'] }}</div>
                <div class="stat-label">Low Risk</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-card text-center">
                <div class="stat-value text-warning">{{ $data['mediumRisk'] }}</div>
                <div class="stat-label">Medium Risk</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-card text-center">
                <div class="stat-value text-danger">{{ $data['highRisk'] }}</div>
                <div class="stat-label">High Risk</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-card text-center">
                <div class="stat-value">{{ $data['averageAttendance'] }}%</div>
                <div class="stat-label">Avg Attendance</div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="stat-card text-center">
                <div class="stat-value">{{ $data['averageMarks'] }}%</div>
                <div class="stat-label">Avg Marks</div>
            </div>
        </div>
    </div>

    {{-- Risk Distribution Chart --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-pie-chart me-1"></i> Risk Distribution</div>
                <div class="card-body">
                    <canvas id="riskChart" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="content-card">
                <div class="card-header"><i class="bi bi-graph-down me-1"></i> Weakest Subjects Across Students</div>
                <div class="card-body">
                    @forelse($data['weakestSubjects'] as $ws)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span style="font-size:.85rem;">{{ $ws['name'] }}</span>
                            <span class="badge bg-danger-subtle text-danger">{{ $ws['average'] }}%</span>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-danger" style="width: {{ $ws['average'] }}%"></div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">All students performing above threshold.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Student Table --}}
    <div class="content-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-people me-1"></i> Student Early Intervention Overview</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Roll No</th>
                            <th>Attendance</th>
                            <th>Average</th>
                            <th>Risk Level</th>
                            <th>Risk Score</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['students'] as $s)
                        <tr>
                            <td class="fw-semibold">{{ $s['model']->user->name }}</td>
                            <td><code>{{ $s['model']->roll_no }}</code></td>
                            <td>
                                <span class="{{ $s['attendance'] < 75 ? 'text-danger fw-bold' : '' }}">{{ $s['attendance'] }}%</span>
                            </td>
                            <td>{{ $s['average'] }}%</td>
                            <td>
                                <span class="badge badge-status risk-{{ $s['risk']->level }}">{{ strtoupper($s['risk']->level) }}</span>
                            </td>
                            <td>{{ $s['risk']->score }}/100</td>
                            <td>
                                <a href="{{ route('teacher.students.show', $s['model']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Details
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Courses --}}
    <div class="content-card">
        <div class="card-header"><i class="bi bi-book me-1"></i> My Courses</div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($data['courses'] as $course)
                <div class="col-md-6 col-lg-3">
                    <div class="border rounded-3 p-3 h-100">
                        <h6 class="fw-bold mb-1">{{ $course->title }}</h6>
                        <small class="text-muted">{{ $course->code }}</small>
                        <div class="mt-2">
                            <a href="{{ route('teacher.courses.show', $course) }}" class="btn btn-sm btn-primary w-100">Manage</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
new Chart(document.getElementById('riskChart'), {
    type: 'doughnut',
    data: {
        labels: ['Low Risk', 'Medium Risk', 'High Risk'],
        datasets: [{
            data: [{{ $data['lowRisk'] }}, {{ $data['mediumRisk'] }}, {{ $data['highRisk'] }}],
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444']
        }]
    },
    options: { responsive: true, cutout: '55%' }
});
</script>
@endpush
