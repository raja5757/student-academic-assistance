@extends('layouts.app')
@section('title', 'Activity Logs')
@section('page-title', 'System Activity Logs')

@section('content')
<div class="animate-in">
    <div class="content-card">
        <div class="card-header"><i class="bi bi-clock-history me-1"></i> Recent Activity</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td>{{ optional($log->user)->name ?? 'System' }}</td>
                            <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                            <td>{{ $log->description }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No activity logs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
