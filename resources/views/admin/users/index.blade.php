@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-people"></i> Quản lý người dùng
            </h1>
            <p class="text-muted">Nâng lên admin hoặc hạ xuống người dùng</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Tìm theo tên hoặc email...">
                </div>
                <div class="col-md-auto">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Tìm
                    </button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">#</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th style="width: 160px;">Vai trò</th>
                            <th style="width: 220px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr>
                                <td>{{ $u->id }}</td>
                                <td>
                                    {{ $u->name }}
                                    @if(auth()->id() === $u->id)
                                        <span class="badge bg-info">Bạn</span>
                                    @endif
                                </td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    <span class="badge {{ $u->role === 'admin' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $u->role === 'admin' ? 'admin' : 'user' }}
                                    </span>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.updateRole', $u) }}" class="d-flex gap-2">
                                        @csrf
                                        @method('PATCH')

                                        <select name="role" class="form-select form-select-sm" {{ auth()->id() === $u->id ? 'disabled' : '' }}>
                                            <option value="user" {{ $u->role === 'user' ? 'selected' : '' }}>user</option>
                                            <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>admin</option>
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary" {{ auth()->id() === $u->id ? 'disabled' : '' }}>
                                            Lưu
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">Không có người dùng</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $users->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
