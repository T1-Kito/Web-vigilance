@extends('layouts.admin')

@section('title', 'Khách hàng')

@section('content')
@php use Illuminate\Support\Str; @endphp
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
            <form method="GET" class="row g-2 mb-3 align-items-end">
                <div class="col-lg-5">
                    <label class="form-label mb-1" style="font-weight:600;">Từ khoá</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Tên / MST / CCCD / Email / SĐT / Đại diện">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="form-label mb-1" style="font-weight:600;">Người đại diện</label>
                    <input type="text" name="rep" class="form-control" value="{{ request('rep') }}" placeholder="Nhập tên người đại diện">
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit" style="white-space:nowrap;"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.customers.index') }}" style="white-space:nowrap;"><i class="bi bi-x-circle me-1"></i>Xóa</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" style="table-layout: fixed; min-width: 1100px;">
                    <thead>
                        <tr>
                            <th style="width:5%;">#</th>
                            <th style="width:26%;">Tên khách hàng</th>
                            <th style="width:11%;">MST/CCCD</th>
                            <th style="width:18%;">Người đại diện</th>
                            <th style="width:25%;">Địa chỉ</th>
                            <th style="width:10%;">SĐT</th>
                            <th style="width:15%;">Email</th>
                            <th style="width:72px;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $i => $c)
                            <tr>
                                <td>{{ $customers->firstItem() + $i }}</td>
                                <td>
                                    <div class="fw-bold" style="word-break: break-word; white-space: normal;">{{ $c->name }}</div>
                                    @if(!empty($c->invoice_recipient))
                                        <div class="small text-muted text-truncate" title="Người nhận HĐ: {{ $c->invoice_recipient }}">
                                            <i class="bi bi-receipt me-1"></i>{{ Str::limit($c->invoice_recipient, 40) }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $c->tax_id }}</td>
                                <td style="word-break: break-word; white-space: normal;">{{ $c->representative }}</td>
                                <td style="word-break: break-word; white-space: normal;">
                                    <div>{{ Str::limit((string) $c->address, 90) }}</div>
                                    @if(!empty($c->tax_address))
                                        <div class="small text-muted">{{ Str::limit((string) $c->tax_address, 90) }}</div>
                                    @endif
                                </td>
                                <td>{{ $c->phone }}</td>
                                <td style="word-break: break-word; white-space: normal;">{{ $c->email }}</td>
                                <td class="text-center align-middle py-2">
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-link text-secondary p-1 rounded-2"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            title="Thao tác"
                                        >
                                            <i class="bi bi-three-dots-vertical fs-5 lh-1"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.customers.edit', $c) }}">
                                                    <i class="bi bi-pencil-square me-2 text-primary"></i>Sửa
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form action="{{ route('admin.customers.destroy', $c) }}" method="POST" onsubmit="return confirm('Xóa khách hàng này?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>Xóa
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <div class="fw-semibold mb-1">Chưa có khách hàng</div>
                                    <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-primary mt-2">Thêm khách hàng</a>
                                </td>
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
