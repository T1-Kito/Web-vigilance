@extends('layouts.admin')

@section('title', 'Nhật ký hoạt động')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-clock-history"></i> Nhật ký hoạt động
            </h1>
            <p class="text-muted">Theo dõi thao tác thêm / sửa / xóa trong admin</p>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Tìm theo email, hành động, module, mô tả...">
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
                            <th style="width: 90px;">#</th>
                            <th style="width: 180px;">Thời gian</th>
                            <th style="width: 220px;">User</th>
                            <th style="width: 140px;">Hành động</th>
                            <th>Đối tượng</th>
                            <th>Mô tả</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                                <td>
                                    <div class="fw-bold">{{ $log->user_email ?? 'N/A' }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ $log->ip_address ?? '' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $log->action }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $log->subject_type ? class_basename($log->subject_type) : 'N/A' }}</div>
                                    <div class="text-muted" style="font-size: 12px;">ID: {{ $log->subject_id ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    {{ $log->description }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Chưa có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
