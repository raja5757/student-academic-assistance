@extends('layouts.app')
@section('title', 'AI Study Planner')
@section('page-title', 'AI Study Planner')

@section('content')
<div class="animate-in">
    {{-- Generate Form --}}
    <div class="content-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-magic me-1"></i> Generate AI Study Plan</span>
            <form method="POST" action="{{ route('study-plans.adapt') }}" class="d-inline">
                @csrf
                <button class="btn btn-sm btn-outline-warning"><i class="bi bi-arrow-repeat me-1"></i> Get Adaptive Advice</button>
            </form>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('study-plans.generate') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Available Hours/Day</label>
                        <input type="number" name="available_hours_per_day" class="form-control" value="3" min="0.5" max="12" step="0.5" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Preferred Study Times</label>
                        <input type="text" name="preferred_times" class="form-control" value="5:00 PM, 8:00 PM" placeholder="e.g. 5:00 PM, 8:00 PM">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-select">
                            <option value="balanced">Balanced</option>
                            <option value="weak_subjects">Focus Weak Subjects</option>
                            <option value="exam_preparation">Exam Preparation</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-stars me-1"></i> Generate Personalized Study Plan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Current Analysis --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-label">Attendance</div>
                <div class="stat-value">{{ $analysis['attendancePercentage'] }}%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-label">Average Marks</div>
                <div class="stat-value">{{ $analysis['averageMarks'] }}%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-label">Study Progress</div>
                <div class="stat-value">{{ $analysis['studyProgress'] }}%</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card text-center">
                <div class="stat-label">Risk Level</div>
                <div class="stat-value"><span class="badge badge-status risk-{{ $analysis['risk']->level }}">{{ strtoupper($analysis['risk']->level) }}</span></div>
            </div>
        </div>
    </div>

    {{-- Existing Plans --}}
    @forelse($plans as $plan)
    <div class="content-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-calendar3 me-1"></i> {{ $plan->title }}
                <small class="text-muted ms-2">{{ $plan->starts_on?->format('M d') }} — {{ $plan->ends_on?->format('M d, Y') }}</small>
            </div>
            @php
                $total = $plan->tasks->count();
                $done = $plan->tasks->where('status', 'completed')->count();
                $pct = $total ? round(($done/$total)*100) : 0;
            @endphp
            <span class="badge bg-primary-subtle text-primary">{{ $pct }}% Complete ({{ $done }}/{{ $total }})</span>
        </div>
        <div class="card-body">
            @if($plan->ai_summary)
                <div class="alert alert-info py-2 mb-3" style="font-size:.84rem;">
                    <i class="bi bi-robot me-1"></i> {{ $plan->ai_summary }}
                </div>
            @endif
            <div class="progress mb-3">
                <div class="progress-bar bg-success" style="width: {{ $pct }}%"></div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Date</th><th>Time</th><th>Subject</th><th>Topic</th><th>Priority</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach($plan->tasks->sortBy('task_date') as $task)
                        <tr class="{{ $task->status === 'completed' ? 'table-success' : '' }}">
                            <td>{{ $task->task_date?->format('D, M d') }}</td>
                            <td><small>{{ $task->start_time }} – {{ $task->end_time }}</small></td>
                            <td>{{ $task->subject?->name ?? '—' }}</td>
                            <td class="fw-semibold">{{ $task->topic }}</td>
                            <td><span class="badge {{ $task->priority === 'high' ? 'bg-danger' : ($task->priority === 'medium' ? 'bg-warning' : 'bg-secondary') }}">{{ $task->priority }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('study-tasks.toggle', $task) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $task->status === 'completed' ? 'btn-success' : 'btn-outline-success' }}">
                                        <i class="bi bi-{{ $task->status === 'completed' ? 'check-circle-fill' : 'circle' }}"></i>
                                        {{ $task->status === 'completed' ? 'Done' : 'Pending' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('study-tasks.destroy', $task) }}" class="d-inline" onsubmit="return confirm('Delete this task?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="content-card">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-plus" style="font-size:3rem; color:var(--text-muted);"></i>
            <h5 class="mt-3">No Study Plans Yet</h5>
            <p class="text-muted">Generate your first AI-powered study plan using the form above.</p>
        </div>
    </div>
    @endforelse
</div>
@endsection
