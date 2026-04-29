@extends('layouts.admin')

@section('title', 'Đơn từ Web')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-0" style="color: #2c3e50; font-weight: 700;">
                <i class="bi bi-file-earmark-text me-2" style="color: #3498db;"></i> Danh sách đơn từ Web
            </h1>
            <p class="text-muted mb-0" style="font-size: 1.1em;">
                Quản lý đơn khách đặt từ website bằng form đồng bộ với báo giá
            </p>
        </div>

        <div class="d-flex gap-3">
            <div class="text-end">
                <div class="h4 mb-0 fw-bold" style="color: #3498db;">{{ $orders->total() }}</div>
                <small class="text-muted">Tổng đơn web</small>
            </div>

            <div class="text-end">
                <div class="h4 mb-0 fw-bold" style="color: #e74c3c;">
                    {{ \App\Models\Order::where('order_code', 'like', 'OD%')->whereIn('status', ['pending','processing'])->count() }}
                </div>
                <small class="text-muted">Đơn đang theo dõi</small>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px;">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.quotes.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="bi bi-file-earmark-text me-1"></i>1. Báo giá
                </a>
                <a href="{{ route('admin.web-orders.index') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-globe2 me-1"></i>Đơn từ Web
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="bi bi-cart-check me-1"></i>2. Đơn hàng
                </a>
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="bi bi-truck me-1"></i>3. Phiếu xuất kho
                </a>
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                    <i class="bi bi-receipt me-1"></i>4. Hóa đơn
                </a>
            </div>
            <div class="small text-muted mt-2">
                Luồng web: Khách đặt từ web -> Admin chỉnh form xử lý -> Duyệt đơn -> Chuyển bước vận hành nội bộ.
            </div>
        </div>
    </div>

    <div class="card shadow" style="border: none; border-radius: 16px; overflow: hidden;">
        <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
            <h6 class="m-0 font-weight-bold" style="font-size: 1.2em;">
                <i class="bi bi-list-ul me-2"></i> Danh sách đơn từ Web
            </h6>
        </div>

        <div class="card-body p-0">
            <form method="GET" action="{{ route('admin.web-orders.index') }}" class="row g-2 mb-3 mx-0 px-3 pt-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Từ khoá</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Mã đơn / Tên / Công ty / MST / SĐT...">
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Tên khách hàng</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ request('customer_name') }}" placeholder="Nhập tên khách hàng">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Mã số thuế</label>
                    <input type="text" name="tax_code" class="form-control" value="{{ request('tax_code') }}" placeholder="MST">
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Trạng thái</label>
                    <select name="status" class="form-select">
                        @foreach($statusOptions as $k => $label)
                            <option value="{{ $k }}" {{ (string) request('status', '') === (string) $k ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-1 col-md-12 d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary flex-grow-1" type="submit" style="white-space:nowrap;"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    @if(request('q') || request('customer_name') || request('tax_code') || request('status'))
                        <a class="btn btn-outline-secondary flex-grow-1" href="{{ route('admin.web-orders.index') }}" style="white-space:nowrap;">
                            <i class="bi bi-x-circle me-1"></i>Xóa
                        </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive quote-table-wrap">
                <table class="table mb-0" style="border: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Mã đơn</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Mã số thuế</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Tên công ty</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Ngày tạo</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Trạng thái</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Tổng tiền</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($orders as $order)
                        @php
                            $total = (int) ($order->items ?? collect())->sum(function ($i) {
                                return (int) ($i->price ?? 0) * (int) ($i->quantity ?? 0);
                            });

                            $statusKey = (string) ($order->status ?? '');
                            $taxCode = (string) ($order->customer_tax_code ?? '');
                            $companyName = (string) ($order->invoice_company_name ?: ($order->receiver_name ?? ''));
                        @endphp

                        <tr class="ao-row" data-href="{{ route('admin.web-orders.show', $order) }}" style="border-bottom: 1px solid #eef2f7;" onmouseover="this.style.backgroundColor='#eaf2ff'" onmouseout="this.style.backgroundColor='white'">
                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600; font-size: 0.9em;">
                                        <i class="bi bi-globe2"></i>
                                    </div>
                                    <div>
                                        <strong style="color: #2c3e50; font-size: 1.1em; white-space: nowrap;">{{ $order->order_code }}</strong>
                                    </div>
                                </div>
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                <div class="fw-semibold" style="color: #2c3e50; font-size: 1.0em;">
                                    {{ $taxCode !== '' ? $taxCode : '...' }}
                                </div>
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                <strong style="color: #2c3e50; font-size: 1.0em;">{{ $companyName !== '' ? $companyName : '...' }}</strong>
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex flex-column">
                                    <span style="font-weight: 600; color: #495057;">{{ optional($order->created_at)->format('d/m/Y') }}</span>
                                    <small class="text-muted">{{ optional($order->created_at)->format('H:i') }}</small>
                                </div>
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                @if($statusKey === 'pending')
                                    <span class="badge" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                        <i class="bi bi-clock me-1"></i>Chờ xử lý
                                    </span>
                                @elseif($statusKey === 'processing')
                                    <span class="badge" style="background: linear-gradient(135deg, #06b6d4 0%, #2563eb 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                        <i class="bi bi-check2-circle me-1"></i>Đã duyệt
                                    </span>
                                @elseif($statusKey === 'cancelled')
                                    <span class="badge" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                        <i class="bi bi-x-circle me-1"></i>Đã hủy
                                    </span>
                                @else
                                    <span class="badge bg-secondary" style="border-radius:20px; padding:8px 16px;">{{ $statusKey }}</span>
                                @endif
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold" style="color: #e74c3c; font-size: 1.2em;">{{ number_format($total, 0, ',', '.') }}đ</span>
                                    <small class="text-muted" style="font-size: 0.85em;">{{ ($order->items ?? collect())->count() }} sản phẩm</small>
                                </div>
                            </td>

                            <td style="border: none; padding: 16px 12px; text-align:center;">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-link text-secondary p-1 rounded-2"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        title="Thao tác"
                                        style="background: rgba(15,23,42,0.05); border-radius: 999px; width: 36px; height: 36px; display:inline-flex; align-items:center; justify-content:center;"
                                    >
                                        <i class="bi bi-three-dots-vertical fs-5 lh-1"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.web-orders.show', $order) }}">
                                                <i class="bi bi-eye me-2 text-primary"></i>Xem chi tiết
                                            </a>
                                        </li>
                                        @if($statusKey === 'pending')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.web-orders.edit', $order) }}">
                                                <i class="bi bi-pencil me-2 text-secondary"></i>Chỉnh sửa
                                            </a>
                                        </li>
                                        @endif

                                        @if($statusKey === 'pending')
                                            <li>
                                                <form method="POST" action="{{ route('admin.web-orders.approve', $order) }}" onsubmit="return confirm('Duyệt đơn web sang trạng thái Đang xử lý?');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i class="bi bi-check2-circle me-2"></i>Duyệt đơn
                                                    </button>
                                                </form>
                                            </li>
                                        @endif


                                        @if(in_array($statusKey, ['pending', 'processing'], true))
                                            <li>
                                                <form method="POST" action="{{ route('admin.web-orders.update-status', $order) }}" onsubmit="return confirm('Chuyển trạng thái sang Đã hủy?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-x-circle me-2"></i>Hủy
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            @if($orders->count() == 0)
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-globe2" style="font-size: 4em; color: #bdc3c7;"></i>
                    </div>
                    <h4 class="text-muted mb-2">Chưa có đơn web</h4>
                    <p class="text-muted">Khi khách đặt hàng từ website, đơn sẽ xuất hiện ở đây.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $orders->links('pagination::bootstrap-5') }}
    </div>
</div>

<style>
    .ao-row { transition: background-color .15s ease; cursor: pointer; }
    .ao-row:hover { background: #eaf2ff; }
    .ao-row:hover td { background: #eaf2ff; }

    .quote-table-wrap td,
    .quote-table-wrap th {
        overflow: visible;
        vertical-align: middle;
    }

    .quote-table-wrap .dropdown {
        position: static;
    }

    .quote-table-wrap .dropdown-menu {
        z-index: 2000 !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ao-row').forEach(function (row) {
        row.addEventListener('click', function (e) {
            const blocked = e.target.closest('a,button,form,input,select,textarea,label,.dropdown-menu,.dropdown-toggle');
            if (blocked) return;

            const href = row.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    });
});
</script>
@endsection
