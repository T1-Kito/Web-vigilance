@extends('layouts.admin')

@section('title', 'Quản lý báo giá')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-0" style="color: #2c3e50; font-weight: 700;">
                <i class="bi bi-file-earmark-text me-2" style="color: #3498db;"></i> Danh sách báo giá
            </h1>
            <p class="text-muted mb-0" style="font-size: 1.1em;">
                Quản lý và xuất PDF cho các báo giá của khách hàng
            </p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.quotes.create') }}" class="btn btn-primary rounded-pill px-4 fw-semibold" style="white-space: nowrap;">
                <i class="bi bi-plus-lg me-2"></i>Tạo báo giá
            </a>
            <div class="d-flex gap-3">
                <div class="text-end">
                    <div class="h4 mb-0 fw-bold" style="color: #3498db;">{{ $orders->total() }}</div>
                    <small class="text-muted">Tổng báo giá</small>
                </div>

                <div class="text-end">
                    <div class="h4 mb-0 fw-bold" style="color: #e74c3c;">
                        {{ \App\Models\Quote::whereIn('status', ['pending','approved'])->count() }}
                    </div>
                    <small class="text-muted">Báo giá đang theo dõi</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px;">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.quotes.index') }}" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bi bi-file-earmark-text me-1"></i>1. Báo giá
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
                Luồng chuẩn: Tạo báo giá -> Duyệt báo giá -> Tạo đơn bán -> Tạo phiếu xuất kho -> Phát hành hóa đơn.
            </div>
        </div>
    </div>

    <div class="card shadow" style="border: none; border-radius: 16px; overflow: hidden;">
        <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
            <h6 class="m-0 font-weight-bold" style="font-size: 1.2em;">
                <i class="bi bi-list-ul me-2"></i> Danh sách báo giá
            </h6>
        </div>

        <div class="card-body p-0">
            <form method="GET" action="{{ route('admin.quotes.index') }}" class="row g-2 mb-3 mx-0 px-3 pt-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Từ khoá</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Mã báo giá / Tên / MST / SĐT...">
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
                            <option value="{{ $k }}" {{ (string) request('status', $defaultStatus) === (string) $k ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-12 d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary flex-grow-1" type="submit" style="white-space:nowrap;"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    @if(request('q') || request('customer_name') || request('tax_code') || request('status'))
                        <a class="btn btn-outline-secondary flex-grow-1" href="{{ route('admin.quotes.index') }}" style="white-space:nowrap;">
                            <i class="bi bi-x-circle me-1"></i>Xóa
                        </a>
                    @endif
                </div>
            </form>

            <div class="table-responsive quote-table-wrap">
                <table class="table mb-0" style="border: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Số báo giá</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Mã số thuế</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Tên công ty</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Ngày báo giá</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Hiệu lực đến</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Trạng thái</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Tổng tiền</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Thao tác</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($orders as $order)
                        @php
                            $orderCode = $order->quote_code ?? ("VK" . str_pad($order->id, 6, '0', STR_PAD_LEFT));
                            $total = (int) ($order->items ?? collect())->sum(function ($i) {
                                return (int) ($i->price ?? 0) * (int) ($i->quantity ?? 0);
                            });

                            $statusKey = (string) ($order->status ?? '');
                            $hasSalesOrder = !empty(optional($order->convertedSalesOrder)->id);
                            $taxCode = (string) ($order->customer_tax_code ?? '');
                            $companyName = (string) ($order->invoice_company_name ?: ($order->receiver_name ?? ''));
                        @endphp

                        <tr class="ao-row" data-href="{{ route('admin.quotes.show', $order) }}" style="border-bottom: 1px solid #eef2f7;" onmouseover="this.style.backgroundColor='#eaf2ff'" onmouseout="this.style.backgroundColor='white'">
                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600; font-size: 0.9em;">
                                        <i class="bi bi-receipt"></i>
                                    </div>
                                    <div>
                                        <strong style="color: #2c3e50; font-size: 1.1em; white-space: nowrap;">{{ $orderCode }}</strong>
                                    </div>
                                </div>
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex align-items-center">
                                    <div class="fw-semibold" style="color: #2c3e50; font-size: 1.0em;">
                                        {{ $taxCode !== '' ? $taxCode : '...' }}
                                    </div>
                                </div>
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                <strong style="color: #2c3e50; font-size: 1.0em;">{{ $companyName !== '' ? $companyName : '...' }}</strong>
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex flex-column">
                                    <span style="font-weight: 600; color: #495057;">{{ optional($order->created_at)->format('d/m/Y') }}</span>
                                </div>
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex flex-column">
                                    <span style="font-weight: 600; color: #495057;">{{ optional($order->valid_until)->format('d/m/Y') ?: '---' }}</span>
                                </div>
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                @if($statusKey === 'pending' || $statusKey === 'processing')
                                    <span class="badge" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                        <i class="bi bi-clock me-1"></i>{{ $statusKey === 'processing' ? 'Đang xử lý' : 'Chờ xử lý' }}
                                    </span>
                                @elseif($statusKey === 'approved')
                                    <span class="badge" style="background: linear-gradient(135deg, #06b6d4 0%, #2563eb 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                        <i class="bi bi-patch-check me-1"></i>Đã duyệt
                                    </span>
                                    <div class="small mt-1">
                                        @if($hasSalesOrder)
                                            <span class="text-success fw-semibold"><i class="bi bi-check2-circle me-1"></i>Đã tạo đơn </span>
                                        @else
                                            <span class="text-secondary"><i class="bi bi-hourglass-split me-1"></i>Chưa tạo đơn </span>
                                        @endif
                                    </div>
                                @elseif($statusKey === 'cancelled')
                                    <span class="badge" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                        <i class="bi bi-x-circle me-1"></i>Đã hủy
                                    </span>
                                @elseif($statusKey === 'lost')
                                    <span class="badge bg-secondary" style="border-radius:20px; padding:8px 16px;">Không chốt</span>
                                @else
                                    <span class="badge" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                        <i class="bi bi-check-circle me-1"></i>Đã tạo đơn bán
                                    </span>
                                @endif
                            </td>

                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex flex-column">
                                    <span class="fw-bold" style="color: #e74c3c; font-size: 1.2em;">{{ number_format($total, 0, ',', '.') }}đ</span>
                                    <small class="text-muted" style="font-size: 0.85em;">{{ ($order->items ?? collect())->count() }} sản phẩm</small>
                                </div>
                            </td>

                            <td style="border: none; padding: 16px 12px; text-align:center;">
                                @php
                                    $quoteEditUrl = route('admin.quotes.edit', $order);
                                @endphp
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
                                            <a class="dropdown-item" href="{{ route('admin.quotes.show', $order) }}">
                                                <i class="bi bi-eye me-2 text-primary"></i>Xem chi tiết
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ $quoteEditUrl }}">
                                                <i class="bi bi-pencil me-2 text-secondary"></i>Chỉnh sửa
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.quotes.destroy', $order) }}" onsubmit="return confirm('Xóa báo giá này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Xóa
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.quotes.destroy', $order) }}" onsubmit="return confirm('XÓA CƯỠNG BỨC: sẽ xóa cả đơn bán + phiếu xuất kho + hóa đơn liên quan. Tiếp tục?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="force_delete" value="1">
                                                <button type="submit" class="dropdown-item text-danger fw-semibold">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>Xóa cưỡng bức
                                                </button>
                                            </form>
                                        </li>
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
                        <i class="bi bi-receipt-cut" style="font-size: 4em; color: #bdc3c7;"></i>
                    </div>
                    <h4 class="text-muted mb-2">Chưa có báo giá</h4>
                    <p class="text-muted">Khi có đơn tạo báo giá, chúng sẽ xuất hiện ở đây.</p>
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

