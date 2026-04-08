@extends('layouts.admin')

@section('title', 'Khách hàng')

@section('content')
@php use Illuminate\Support\Str; @endphp
<div class="container-fluid py-4">
    <div class="mb-4 d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <div>
            <h2 class="mb-1">Quản lý khách hàng</h2>
            <div class="text-muted">Theo dõi hồ sơ khách hàng, trạng thái hoạt động và thao tác nhanh chứng từ.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
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

    <div class="row g-3 mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Tổng khách hàng</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Khách mới (30 ngày)</div>
                    <div class="fs-4 fw-bold text-primary">{{ number_format($stats['new_30_days'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Khách doanh nghiệp</div>
                    <div class="fs-4 fw-bold text-info">{{ number_format($stats['company'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Đang hoạt động</div>
                    <div class="fs-4 fw-bold text-success">{{ number_format($stats['active'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3 align-items-end">
                <div class="col-lg-3">
                    <label class="form-label mb-1 fw-semibold">Từ khoá</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Tên / MST / SĐT / Email">
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="form-label mb-1 fw-semibold">Người đại diện</label>
                    <input type="text" name="rep" class="form-control" value="{{ request('rep') }}" placeholder="Tên đại diện">
                </div>
                <div class="col-lg-2">
                    <label class="form-label mb-1 fw-semibold">Loại khách</label>
                    <select name="customer_type" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="company" @selected(request('customer_type') === 'company')>Doanh nghiệp</option>
                        <option value="individual" @selected(request('customer_type') === 'individual')>Cá nhân</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label mb-1 fw-semibold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach(($statusOptions ?? collect()) as $st)
                            <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label mb-1 fw-semibold">Loại hình DN</label>
                    <select name="biz_type" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach(($bizTypeOptions ?? collect()) as $biz)
                            <option value="{{ $biz }}" @selected(request('biz_type') === $biz)>{{ $biz }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 d-flex gap-2">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel"></i></button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.customers.index') }}"><i class="bi bi-x-circle"></i></a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle" style="min-width: 1100px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width:4%;">#</th>
                            <th style="width:28%;">Khách hàng</th>
                            <th style="width:12%;">MST/CCCD</th>
                            <th style="width:13%;">Người phụ trách</th>
                            <th style="width:14%;">Liên hệ</th>
                            <th style="width:14%;">Trạng thái</th>
                            <th style="width:9%;">Ngày tạo</th>
                            <th style="width:72px;" class="text-center">...</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $i => $c)
                            @php
                                $isActive = blank($c->company_status) || Str::contains(Str::lower((string)$c->company_status), 'hoạt động');
                                $isCompany = !blank($c->tax_id);
                            @endphp
                            <tr>
                                <td>{{ $customers->firstItem() + $i }}</td>
                                <td>
                                    <div class="fw-semibold text-dark" title="{{ $c->name }}">{{ Str::limit($c->name, 55) }}</div>
                                    <div class="small text-muted d-flex gap-2 flex-wrap">
                                        <span class="badge {{ $isCompany ? 'bg-primary-subtle text-primary' : 'bg-secondary-subtle text-secondary' }}">{{ $isCompany ? 'Doanh nghiệp' : 'Cá nhân' }}</span>
                                        @if(!empty($c->invoice_recipient))
                                            <span title="{{ $c->invoice_recipient }}"><i class="bi bi-person-vcard me-1"></i>{{ Str::limit($c->invoice_recipient, 26) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $c->tax_id ?: '---' }}</td>
                                <td>{{ $c->representative ?: '---' }}</td>
                                <td>
                                    <div>{{ $c->phone ?: '---' }}</div>
                                    <div class="small text-muted" title="{{ $c->email }}">{{ $c->email ? Str::limit($c->email, 26) : '---' }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $isActive ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                        {{ $c->company_status ?: 'Đang hoạt động' }}
                                    </span>
                                </td>
                                <td>{{ optional($c->created_at)->format('d/m/Y') }}</td>
                                <td class="text-center py-2">
                                    <div class="dropdown">
                                        <button class="btn btn-link text-secondary p-1 rounded-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Thao tác">
                                            <i class="bi bi-three-dots-vertical fs-5 lh-1"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.customers.show', $c) }}">
                                                    <i class="bi bi-person-lines-fill me-2 text-info"></i>Hồ sơ 360
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.customers.edit', $c) }}">
                                                    <i class="bi bi-pencil-square me-2 text-primary"></i>Sửa khách hàng
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
                                <td colspan="8" class="text-center text-muted py-4">
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
