@extends('layouts.admin')

@section('title', 'Chi tiết khách đặt hàng')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-0" style="color:#0f172a;font-weight:800;">
                <i class="bi bi-file-earmark-text me-2" style="color:#2563eb;"></i>Chi tiết khách đặt hàng
            </h1>
            <p class="text-muted mb-0">
                Khóa khách: <span class="fw-semibold">{{ $customerKey }}</span>
            </p>
        </div>
        <a class="btn btn-outline-secondary rounded-pill" href="{{ route('admin.customer-order-info.index') }}">
            <i class="bi bi-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="fw-bold text-dark mb-2"><i class="bi bi-person me-2 text-primary"></i>Thông tin khách</div>
                    <div class="mb-2">
                        <div class="text-muted small">Tên</div>
                        <div class="fw-semibold">{{ $customer->receiver_name ?? '—' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small">MST</div>
                        <div class="fw-semibold">{{ $customer->customer_tax_code ?? '—' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small">SĐT</div>
                        <div class="fw-semibold">{{ $customer->receiver_phone ?? '—' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small">Email</div>
                        <div class="fw-semibold">{{ $customer->customer_email ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-muted small">Địa chỉ</div>
                        <div class="fw-semibold" style="word-break: break-word;">{{ $customer->receiver_address ?? $customer->invoice_address ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <div class="fw-bold text-dark mb-2"><i class="bi bi-clock-history me-2 text-primary"></i>Danh sách đơn hàng</div>
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <thead>
                                <tr style="background:#f8fafc;">
                                    <th style="border:none;">Mã đơn</th>
                                    <th style="border:none;">Ngày</th>
                                    <th style="border:none;">Trạng thái</th>
                                    <th style="border:none; text-end;">Tổng tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($orders as $o)
                                    @php
                                        $items = $o->items ?? collect();
                                        $total = (float) $items->sum(function($i){ return (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0); });
                                        $status = (string) ($o->status ?? '');
                                        $badgeClass = match($status){
                                            'pending','processing' => 'bg-warning',
                                            'completed' => 'bg-success',
                                            'cancelled' => 'bg-danger',
                                            default => 'bg-secondary',
                                        };
                                        $label = match($status){
                                            'pending' => 'Chờ xử lý',
                                            'processing' => 'Đang xử lý',
                                            'completed' => 'Hoàn thành',
                                            'cancelled' => 'Đã hủy',
                                            default => $status,
                                        };
                                    @endphp
                                    <tr>
                                        <td style="border:none;">
                                            @php
                                                $code = $o->order_code ?? ("VK".str_pad($o->id, 6, '0', STR_PAD_LEFT));
                                                $hasQuoteCode = !empty($o->order_code);
                                            @endphp
                                            @if($hasQuoteCode)
                                                <a
                                                    href="{{ route('orders.quote', ['orderCode' => $o->order_code]) }}"
                                                    target="_blank"
                                                    rel="noopener"
                                                    class="fw-semibold text-decoration-none"
                                                    style="color:#2563eb;"
                                                    title="Xem bảng báo giá"
                                                >
                                                    {{ $code }}
                                                </a>
                                            @else
                                                <div class="fw-semibold">{{ $code }}</div>
                                            @endif
                                        </td>
                                        <td style="border:none;">
                                            {{ $o->created_at?->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}
                                        </td>
                                        <td style="border:none;">
                                            <span class="badge {{ $badgeClass }}" style="border-radius:999px;padding:.5rem .75rem;">
                                                {{ $label }}
                                            </span>
                                        </td>
                                        <td style="border:none; text-end; color:#e74c3c; font-weight:800;">
                                            {{ number_format((int) $total, 0, ',', '.') }}đ
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">Khách chưa có đơn.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-center mt-4">
                        {{ $orders->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

