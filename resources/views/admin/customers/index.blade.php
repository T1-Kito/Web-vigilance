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
                        <option value="Khách hàng đại lý cấp 1" @selected(request('customer_type') === 'Khách hàng đại lý cấp 1')>Đại lý cấp 1</option>
                        <option value="Khách hàng đại lý cấp 2" @selected(request('customer_type') === 'Khách hàng đại lý cấp 2')>Đại lý cấp 2</option>
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

            <div class="table-responsive customer-grid-wrap">
                <table class="table customer-grid mb-0" style="min-width: 1000px;">
                    <thead>
                        <tr>
                            <th style="width:34px;"><input type="checkbox" class="form-check-input" disabled></th>
                            <th style="width:190px;">Mã KH</th>
                            <th style="width:170px;">Loại khách hàng</th>
                            <th>Tên khách hàng</th>
                            <th style="width:150px;">MST/CCCD</th>
                            <th style="width:90px;" class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $i => $c)
                            @php
                                $customerType = trim((string) ($c->customer_type ?? ''));
                                $fallbackType = !blank($c->tax_id) ? 'Khách hàng doanh nghiệp' : 'Khách hàng cá nhân';
                                $typeLabel = $customerType !== '' ? $customerType : $fallbackType;
                                $customerCode = 'KH' . str_pad((string) $c->id, 8, '0', STR_PAD_LEFT);
                                $typeClass = match ($typeLabel) {
                                    'Khách hàng đại lý cấp 1' => 'type-chip type-agent-1',
                                    'Khách hàng đại lý cấp 2' => 'type-chip type-agent-2',
                                    'Khách hàng cá nhân' => 'type-chip type-personal',
                                    default => 'type-chip type-company',
                                };
                            @endphp
                            <tr class="customer-row-link" data-href="{{ route('admin.customers.show', $c) }}">
                                <td><input type="checkbox" class="form-check-input" disabled></td>
                                <td class="fw-semibold text-primary">{{ $customerCode }}</td>
                                <td><span class="{{ $typeClass }}">{{ $typeLabel }}</span></td>
                                <td>
                                    <a href="{{ route('admin.customers.show', $c) }}" class="customer-name-link" title="{{ $c->name }}">
                                        {{ Str::upper(Str::limit($c->name, 58)) }}
                                    </a>
                                </td>
                                <td>{{ $c->tax_id ?: '-' }}</td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a href="{{ route('admin.customers.edit', $c) }}" class="icon-btn" title="Sửa"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.customers.destroy', $c) }}" method="POST" onsubmit="return confirm('Xóa khách hàng này?');" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn text-danger" title="Xóa"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <div class="fw-semibold mb-1">Chưa có khách hàng</div>
                                    <a href="{{ route('admin.customers.create') }}" class="btn btn-sm btn-primary mt-2">Thêm khách hàng</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $customers->links() }}</div>
        </div>
    </div>
</div>

<style>
.customer-grid-wrap {
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}

.customer-grid thead th {
    background: #f6f7fb;
    color: #5b6477;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .2px;
    border-bottom: 1px solid #dfe3ec;
    padding: 10px 12px;
    white-space: nowrap;
}

.customer-grid tbody td {
    border-bottom: 1px solid #eceff5;
    padding: 9px 12px;
    font-size: 14px;
    color: #2f3747;
    vertical-align: middle;
}

.customer-grid tbody tr {
    transition: background-color .15s ease;
}

.customer-grid tbody tr:hover > * {
    background: #e8f3ff !important;
}

.customer-row-link {
    cursor: pointer;
}

.customer-name-link {
    color: #1f4ba5;
    text-decoration: none;
    font-weight: 600;
}

.customer-name-link:hover {
    text-decoration: underline;
}

.type-chip {
    display: inline-block;
    white-space: nowrap;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.35;
    padding: 0;
    border-radius: 0;
    background: transparent !important;
}

.type-company {
    color: #1d4ed8;
}

.type-agent-1 {
    color: #b45309;
}

.type-agent-2 {
    color: #0f766e;
}

.type-personal {
    color: #4b5563;
}

.icon-btn {
    border: none;
    background: #f1f3f8;
    color: #6b7280;
    width: 26px;
    height: 26px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}

.icon-btn:hover {
    background: #e5e9f2;
    color: #374151;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.customer-row-link').forEach(function (row) {
        row.addEventListener('click', function (event) {
            const target = event.target;
            if (target.closest('a') || target.closest('button') || target.closest('form') || target.closest('input') || target.closest('select') || target.closest('textarea') || target.closest('label')) {
                return;
            }
            const href = row.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });
});
</script>
@endsection
