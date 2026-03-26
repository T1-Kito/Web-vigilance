@extends('layouts.admin')

@section('title', 'Khách hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4" style="display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap;">
        <h2 class="mb-0">Khách hàng</h2>
        <div style="display:flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.customers.import.form') }}" class="btn btn-outline-primary"><i class="bi bi-upload me-1"></i>Import Excel</a>
            <a href="{{ route('admin.customers.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i>Thêm khách hàng</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Tìm theo tên / MST/CCCD / Email / SĐT">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-outline-primary" type="submit">Tìm</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.customers.index') }}">Xóa lọc</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" style="table-layout: fixed;">
                    <thead>
                        <tr>
                            <th style="width:5%;">#</th>
                            <th style="width:18%;">Tên khách hàng</th>
                            <th style="width:11%;">MST/CCCD</th>
                            <th style="width:22%;">Địa chỉ</th>
                            <th style="width:12%;">Người nhận HĐ</th>
                            <th style="width:18%;">Email</th>
                            <th style="width:9%;">Số điện thoại</th>
                            <th style="width:5%;" class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $i => $c)
                            <tr>
                                <td>{{ $customers->firstItem() + $i }}</td>
                                <td>
                                    <div class="fw-bold">{{ $c->name }}</div>
                                </td>
                                <td>{{ $c->tax_id }}</td>
                                <td style="word-break: break-word; white-space: normal;">{{ $c->address }}</td>
                                <td>{{ $c->invoice_recipient }}</td>
                                <td style="word-break: break-word; white-space: normal;">{{ $c->email }}</td>
                                <td>{{ $c->phone }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.customers.show', $c) }}">Xem</a>
                                    <a class="btn btn-sm btn-primary" href="{{ route('admin.customers.edit', $c) }}">Sửa</a>
                                    <form action="{{ route('admin.customers.destroy', $c) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Xóa khách hàng này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">Chưa có khách hàng</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection
