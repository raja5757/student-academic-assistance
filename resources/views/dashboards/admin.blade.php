@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="animate-in">
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="stat-value">{{ $data['totalStudents'] }}</div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-person-workspace"></i></div>
                    <div>
                        <div class="stat-value">{{ $data['totalTeachers'] }}</div>
                        <div class="stat-label">Teachers</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-book"></i></div>
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
                    <div class="stat-icon risk-high"><i class="bi bi-exclamation-triangle"></i></div>
                    <div>
                        <div class="stat-value">{{ $data['highRisk'] }}</div>
                        <div class="stat-label">High Risk Students</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Risk Distribution --}}
        <div class="col-lg-4">
            <div class="content-card h-100">
                <div class="card-header"><i class="bi bi-pie-chart me-1"></i> Risk Distribution</div>
                <div class="card-body">
                    <canvas id="riskChart" height="200"></canvas>
                    <div class="mt-3 d-flex justify-content-around text-center">
                        <div><span class="badge bg-success">{{ $data['riskDistribution']['low'] }}</span><br><small class="text-muted">Low</small></div>
                        <div><span class="badge bg-warning">{{ $data['riskDistribution']['medium'] }}</span><br><small class="text-muted">Medium</small></div>
                        <div><span class="badge bg-danger">{{ $data['riskDistribution']['high'] }}</span><br><small class="text-muted">High</small></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent High Risk --}}
        <div class="col-lg-8">
            <div class="content-card h-100">
                <div class="card-header"><i class="bi bi-exclamation-octagon me-1"></i> Recent Risk Scores</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr><th>Student</th><th>Score</th><th>Level</th><th>Date</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                @foreach($data['recentRisks'] as $risk)
                                <tr>
                                    <td class="fw-semibold">{{ $risk->student?->user?->name ?? 'N/A' }}</td>
                                    <td>{{ $risk->score }}/100</td>
                                    <td><span class="badge badge-status risk-{{ $risk->level }}">{{ strtoupper($risk->level) }}</span></td>
                                    <td><small class="text-muted">{{ $risk->calculated_at?->format('M d, Y') }}</small></td>
                                    <td>
                                        @if($risk->student)
                                            <a href="{{ route('admin.students.show', $risk->student) }}" class="btn btn-sm btn-outline-primary">View</a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="content-card">
        <div class="card-header d-flex justify-content-between">
            <span><i class="bi bi-person-lines-fill me-1"></i> Recent Users</span>
            <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr>
                    </thead>
                    <tbody>
                        @foreach($data['users'] as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td><small>{{ $user->email }}</small></td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $user->role?->name }}</span></td>
                            <td><span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $user->status }}</span></td>
                            <td><small class="text-muted">{{ $user->created_at->format('M d, Y') }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
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
        labels: ['Low', 'Medium', 'High'],
        datasets: [{ data: [{{ $data['riskDistribution']['low'] }}, {{ $data['riskDistribution']['medium'] }}, {{ $data['riskDistribution']['high'] }}], backgroundColor: ['#10b981','#f59e0b','#ef4444'] }]
    },
    options: { responsive: true, cutout: '55%' }
});
</script>
@endpush
