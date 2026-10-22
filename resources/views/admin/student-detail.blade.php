@extends('layouts.app')
@section('title', 'Student: ' . $student->user->name)
@section('page-title', 'Student Detail — ' . $student->user->name)

@section('content')
<div class="animate-in">
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="content-card">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:70px;height:70px;font-size:1.8rem;"><i class="bi bi-person-fill"></i></div>
                    <h5 class="fw-bold mb-1">{{ $student->user->name }}</h5>
                    <p class="text-muted mb-1" style="font-size:.84rem;">{{ $student->roll_no }} • {{ $student->program }}</p>
                    <p class="text-muted mb-0" style="font-size:.84rem;">{{ $student->department?->name }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="row g-3">
                <div class="col-6 col-md-3"><div class="stat-card text-center"><div class="stat-value">{{ $data['attendancePercentage'] }}%</div><div class="stat-label">Attendance</div></div></div>
                <div class="col-6 col-md-3"><div class="stat-card text-center"><div class="stat-value">{{ $data['averageMarks'] }}%</div><div class="stat-label">Avg Marks</div></div></div>
                <div class="col-6 col-md-3"><div class="stat-card text-center"><div class="stat-value">{{ $data['studyProgress'] }}%</div><div class="stat-label">Study Progress</div></div></div>
                <div class="col-6 col-md-3"><div class="stat-card text-center"><span class="badge badge-status risk-{{ $data['risk']->level }}" style="font-size:1rem;">{{ strtoupper($data['risk']->level) }}</span><div class="stat-label mt-1">Risk: {{ $data['risk']->score }}/100</div></div></div>
            </div>
            <div class="content-card mt-3"><div class="card-body"><h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle me-1"></i> Risk Factors</h6><ul class="mb-0">@foreach($data['risk']->reasons ?? [] as $r)<li style="font-size:.85rem;">{{ $r }}</li>@endforeach</ul></div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="content-card"><div class="card-header"><i class="bi bi-bar-chart me-1"></i> Subject Performance</div><div class="card-body"><canvas id="subjectChart" height="200"></canvas></div></div>
        </div>
        <div class="col-lg-6">
            <div class="content-card"><div class="card-header"><i class="bi bi-lightbulb me-1"></i> AI Recommendations</div><div class="card-body">
                @forelse($data['recommendations'] as $rec)
                <div class="d-flex gap-2 mb-3 pb-2 border-bottom"><span class="badge badge-status risk-{{ $rec->priority === 'high' ? 'high' : 'medium' }}" style="height:fit-content;">{{ $rec->priority }}</span><div><div class="fw-semibold" style="font-size:.86rem;">{{ $rec->title }}</div><small class="text-muted">{{ $rec->body }}</small></div></div>
                @empty<p class="text-muted">No recommendations yet.</p>@endforelse
            </div></div>
        </div>
    </div>

    <div class="content-card"><div class="card-header"><i class="bi bi-journal-bookmark me-1"></i> Marks</div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>Subject</th><th>Type</th><th>Title</th><th>Score</th><th>%</th></tr></thead><tbody>
        @foreach($student->marks->sortByDesc('recorded_on') as $mark)
        <tr><td>{{ $mark->subject?->name }}</td><td><span class="badge bg-primary-subtle text-primary">{{ ucfirst($mark->type) }}</span></td><td>{{ $mark->title }}</td><td>{{ $mark->obtained_marks }}/{{ $mark->total_marks }}</td><td>{{ round(($mark->obtained_marks/max(1,$mark->total_marks))*100) }}%</td></tr>
        @endforeach
    </tbody></table></div></div></div>
</div>
@endsection

@push('scripts')
<script>
const subjectData = @json($data['subjectPerformance']);
new Chart(document.getElementById('subjectChart'), {
    type: 'bar', data: { labels: subjectData.map(s=>s.name), datasets: [{ label: 'Average %', data: subjectData.map(s=>s.average), backgroundColor: subjectData.map(s=>s.average>=75?'#10b981':s.average>=60?'#f59e0b':'#ef4444'), borderRadius: 6 }] },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100 } } }
});
</script>
@endpush
