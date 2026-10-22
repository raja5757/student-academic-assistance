@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')
<div class="animate-in">
    <div class="content-card">
        <div class="card-header"><i class="bi bi-people me-1"></i> All Users</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th><th>Action</th></tr></thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td><small>{{ $user->email }}</small></td>
                            <td><span class="badge bg-primary-subtle text-primary">{{ $user->role?->name }}</span></td>
                            <td><span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $user->status }}</span></td>
                            <td><small class="text-muted">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</small></td>
                            <td>
                                <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm {{ $user->status === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                        {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
