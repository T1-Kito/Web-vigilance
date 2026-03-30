@extends('layouts.admin')

@section('title', 'Quản lý thông tin khách đặt hàng')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-0" style="color:#0f172a;font-weight:800;">
                <i class="bi bi-people me-2" style="color:#2563eb;"></i>Quản lý thông tin khách đặt hàng
            </h1>
            <p class="text-muted mb-0">Tổng hợp khách theo MST/SĐT (từ lịch sử đơn hàng).</p>
        </div>
        <form method="GET" action="{{ route('admin.customer-order-info.index') }}" class="d-flex gap-2 flex-wrap align-items-end">
            <div class="input-group" style="min-width: 360px;">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Nhập MST / SĐT / Tên...">
                <button class="btn btn-primary" type="submit" title="Tìm kiếm" style="padding-left: 12px; padding-right: 12px;">
                    <i class="bi bi-search"></i>
                </button>
            </div>
            @if($q !== '')
                <a class="btn btn-outline-secondary" href="{{ route('admin.customer-order-info.index') }}">Xóa</a>
            @endif
        </form>
    </div>

    <div class="card shadow-sm border-0" style="border-radius:16px; overflow:hidden;">
        <div class="card-header py-3" style="background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color:#fff; border:0;">
            <h6 class="m-0 fw-bold"><i class="bi bi-table me-2"></i>Danh sách khách</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 ao-customer-order-table">
                    <thead>
                        <tr style="background:#f8fafc;">
                            <th style="border: none;">Khách</th>
                            <th style="border: none;">MST</th>
                            <th style="border: none;">SĐT</th>
                            <th style="border: none;">Số đơn</th>
                            <th style="border: none;">Tổng tiền</th>
                            <th style="border: none;">Lần mua gần nhất</th>
                            <th style="border: none; width:90px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $c)
                            @php
                                $total = (float) ($c->total_amount ?? 0);
                                $last = $c->last_order_at
                                    ? \Carbon\Carbon::parse($c->last_order_at, 'UTC')->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i')
                                    : '—';
                            @endphp
                            <tr
                                class="ao-click-row"
                                data-href="{{ route('admin.customer-order-info.show', ['customerKey' => $c->customer_key]) }}"
                                role="link"
                                tabindex="0"
                                style="transition: all .2s ease;"
                            >
                                <td style="border:none;">
                                    <div class="fw-semibold text-dark">{{ $c->receiver_name ?: '—' }}</div>
                                    <div class="text-muted small">{{ $c->invoice_company_name ?: '' }}</div>
                                </td>
                                <td style="border:none;">{{ $c->customer_tax_code ?: '—' }}</td>
                                <td style="border:none;">{{ $c->receiver_phone ?: '—' }}</td>
                                <td style="border:none;">
                                    <span class="badge bg-primary" style="border-radius:999px; padding:.5rem .8rem;">{{ $c->orders_count }}</span>
                                </td>
                                <td style="border:none; color:#e74c3c; font-weight:800;">
                                    {{ number_format((int) $total, 0, ',', '.') }}đ
                                </td>
                                <td style="border:none;">{{ $last }}</td>
                                <td style="border:none;">
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
                                                <a
                                                    class="dropdown-item"
                                                    href="{{ route('admin.customer-order-info.show', ['customerKey' => $c->customer_key]) }}"
                                                >
                                                    <i class="bi bi-eye me-2 text-primary"></i>Xem chi tiết
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.customer-order-info.destroy', ['customerKey' => $c->customer_key]) }}"
                                                    onsubmit="return confirm('Xóa tất cả đơn của khách này? Hành động này không thể hoàn tác.');"
                                                >
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
                                <td colspan="7" class="text-center text-muted py-5">Chưa có dữ liệu khách đặt hàng.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Không dùng pagination ở đây (simple query + limit) --}}
</div>

    <style>
        .ao-customer-order-table {
            border-collapse: collapse;
        }

        .ao-customer-order-table tbody tr.ao-click-row {
            cursor: pointer;
            border-bottom: 1px dashed rgba(15, 23, 42, 0.16);
        }

        .ao-customer-order-table tbody tr.ao-click-row:hover {
            background: #f8fafc;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('tr.ao-click-row[data-href]').forEach(function (tr) {
                var href = tr.getAttribute('data-href');

                tr.addEventListener('click', function (ev) {
                    // Nếu bấm vào dropdown hoặc nút/link/form thì để hành động đó chạy bình thường.
                    if (ev.target.closest('.dropdown')) return;
                    if (ev.target.closest('form')) return;
                    if (ev.target.closest('a')) return;
                    if (ev.target.closest('button')) return;

                    if (href) window.location.href = href;
                });

                tr.addEventListener('keydown', function (ev) {
                    if (ev.key === 'Enter' || ev.key === ' ') {
                        ev.preventDefault();
                        if (href) window.location.href = href;
                    }
                });
            });
        });
    </script>
@endsection

