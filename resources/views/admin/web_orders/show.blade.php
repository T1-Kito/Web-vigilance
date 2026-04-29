@extends('layouts.admin')

@section('title', 'Chi tiết đơn từ Web')

@section('content')
@php
    $orderCode = $order->order_code ?? ('OD' . str_pad($order->id, 6, '0', STR_PAD_LEFT));
    $items = $order->items ?? collect();
    $subTotal = (float) $items->sum(fn($i) => (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0));
    $discount = (float) ($order->discount_percent ?? 0);
    $vat = (float) ($order->vat_percent ?? 8);
    $afterDiscount = max(0, $subTotal * (1 - $discount / 100));
    $vatAmount = $afterDiscount * ($vat / 100);
    $total = $afterDiscount + $vatAmount;

    $statusMap = [
        'pending' => ['label' => 'Chờ xử lý', 'class' => 'warning'],
        'processing' => ['label' => 'Đã duyệt', 'class' => 'info'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'danger'],
    ];
    $status = $statusMap[$order->status] ?? ['label' => (string) $order->status, 'class' => 'secondary'];

    $paymentTerm = (string) ($order->payment_term ?? 'full_advance');
    $paymentTermLabel = match ($paymentTerm) {
        'debt' => 'Công nợ theo hạn',
        'deposit' => 'Đặt cọc + phần còn lại',
        default => 'Thanh toán 100% trước giao hàng',
    };

    $salesOrderFromWeb = \App\Models\SalesOrder::query()->where('source_order_id', $order->id)->first();
    $deliveries = collect();
    $invoices = collect();
    if ($salesOrderFromWeb) {
        $deliveries = \App\Models\Delivery::query()->where('sales_order_id', $salesOrderFromWeb->id)->orderByDesc('created_at')->get();
        $invoices = \App\Models\Invoice::query()->where('sales_order_id', $salesOrderFromWeb->id)->orderByDesc('created_at')->get();
    }

    $stepReceived = true;
    $stepApproved = (string) $order->status === 'processing' || (bool) $salesOrderFromWeb;
    $stepSalesOrder = (bool) $salesOrderFromWeb;
    $stepDelivery = $deliveries->count() > 0;
    $stepInvoice = $invoices->count() > 0;
@endphp

<div class="container-fluid py-4">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Chi tiết đơn web: {{ $orderCode }}</h1>
            <div class="text-muted">Khách hàng: {{ $order->invoice_company_name ?: $order->receiver_name }}</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.web-orders.pdf', $order) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary">
                <i class="bi bi-download me-1"></i>Tải PDF
            </a>
            <button type="button" class="btn btn-outline-info" id="btnSendZaloCopy">
                <i class="bi bi-chat-dots me-1"></i>Gửi Zalo
            </button>
            @if((string) $order->status === 'pending')
                <a href="{{ route('admin.web-orders.edit', $order) }}" class="btn btn-primary">Chỉnh sửa</a>
            @endif

            @if((string) $order->status === 'pending')
                <form method="POST" action="{{ route('admin.web-orders.approve', $order) }}" onsubmit="return confirm('Duyệt đơn web sang trạng thái Đang xử lý?');">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i>Duyệt đơn
                    </button>
                </form>
            @endif

            @if((string) $order->status === 'processing' && !$salesOrderFromWeb)
                <button
                    type="button"
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#autoCreateOrderModal"
                >
                    <i class="bi bi-magic me-1"></i>Sinh đơn hàng tự động
                </button>
            @endif

            @if(in_array((string) $order->status, ['pending', 'processing'], true))
                <form method="POST" action="{{ route('admin.web-orders.update-status', $order) }}" onsubmit="return confirm('Chuyển trạng thái sang Đã hủy?');">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-1"></i>Hủy
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.web-orders.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3 small">
                <span class="badge {{ $stepReceived ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} px-3 py-2">1. Tiếp nhận đơn web</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge {{ $stepApproved ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} px-3 py-2">2. Duyệt đơn web</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge {{ $stepSalesOrder ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} px-3 py-2">3. Sinh đơn hàng tự động</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge {{ $stepDelivery ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} px-3 py-2">4. Xuất kho</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge {{ $stepInvoice ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} px-3 py-2">5. Hóa đơn</span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Thông tin khách hàng</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="text-muted small">Tên công ty</div><div class="fw-semibold">{{ $order->invoice_company_name ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Mã số thuế</div><div class="fw-semibold">{{ $order->customer_tax_code ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Người liên hệ</div><div class="fw-semibold">{{ $order->customer_contact_person ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">SĐT liên hệ</div><div class="fw-semibold">{{ $order->customer_phone ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Email</div><div class="fw-semibold">{{ $order->customer_email ?: '---' }}</div></div>
                        <div class="col-12"><div class="text-muted small">Địa chỉ hóa đơn</div><div class="fw-semibold">{{ $order->invoice_address ?: '---' }}</div></div>

                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-12"><div class="fw-bold">Thông tin người nhận</div></div>
                        <div class="col-md-6"><div class="text-muted small">Người nhận</div><div class="fw-semibold">{{ $order->receiver_name ?: ($order->customer_contact_person ?: '---') }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">SĐT người nhận</div><div class="fw-semibold">{{ $order->receiver_phone ?: ($order->customer_phone ?: '---') }}</div></div>
                        <div class="col-12"><div class="text-muted small">Địa chỉ giao hàng</div><div class="fw-semibold">{{ $order->receiver_address ?: ($order->invoice_address ?: '---') }}</div></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Điều khoản thanh toán</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Hình thức</div>
                            <div class="fw-semibold">{{ $paymentTermLabel }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Hạn công nợ</div>
                            <div class="fw-semibold">{{ $paymentTerm === 'debt' ? (($order->payment_due_days ?? 0) . ' ngày') : '---' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Tỷ lệ đặt cọc</div>
                            <div class="fw-semibold">{{ $paymentTerm === 'deposit' ? (rtrim(rtrim(number_format((float) ($order->deposit_percent ?? 0), 2, '.', ''), '0'), '.') . '%') : '---' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Ghi chú thanh toán</div>
                            <div class="fw-semibold">{{ $order->payment_note ?: '---' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Danh sách sản phẩm</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3">Sản phẩm</th>
                                    <th style="width:120px;">Đơn vị</th>
                                    <th style="width:100px;">SL</th>
                                    <th style="width:160px;">Đơn giá</th>
                                    <th style="width:170px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    @php $lineTotal = (float) $item->price * (int) $item->quantity; @endphp
                                    <tr>
                                        <td class="ps-3"><div class="fw-semibold">{{ $item->product->name ?? ('Sản phẩm #' . $item->product_id) }}</div></td>
                                        <td>{{ $item->unit ?: '---' }}</td>
                                        <td>{{ (int) $item->quantity }}</td>
                                        <td>{{ number_format((float) $item->price, 0, ',', '.') }}đ</td>
                                        <td class="fw-semibold">{{ number_format($lineTotal, 0, ',', '.') }}đ</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Không có sản phẩm.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 16px;">
                <div class="card-header bg-white fw-bold">Thông tin đơn web</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Mã đơn</span><span class="fw-semibold">{{ $orderCode }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Ngày tạo</span><span class="fw-semibold">{{ optional($order->created_at)->format('d/m/Y H:i') }}</span></div>
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="text-muted">Trạng thái</span>
                        <div class="text-end">
                            <span class="badge bg-{{ $status['class'] }}">{{ $status['label'] }}</span>
                            @if((string) $order->status === 'pending')
                                <form method="POST" action="{{ route('admin.web-orders.update-status', $order) }}" class="mt-2 d-flex align-items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm" style="min-width: 170px;">
                                        @foreach([
                                            'pending' => 'Chờ xử lý',
                                            'cancelled' => 'Đã hủy',
                                        ] as $key => $label)
                                            <option value="{{ $key }}" @selected((string) $order->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Lưu</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Staff code</span><span class="fw-semibold">{{ $order->staff_code ?: '---' }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Sales</span><span class="fw-semibold">{{ $order->sales_name ?: '---' }}</span></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-1"><span>Tạm tính</span><strong>{{ number_format($subTotal, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span>Chiết khấu ({{ rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') }}%)</span><strong>{{ number_format($subTotal - $afterDiscount, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span>VAT ({{ rtrim(rtrim(number_format($vat, 2, '.', ''), '0'), '.') }}%)</span><strong>{{ number_format($vatAmount, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between pt-2 border-top"><span class="fw-semibold">Tổng cộng</span><strong class="text-danger">{{ number_format($total, 0, ',', '.') }}đ</strong></div>
                    <hr>
                    <div class="text-muted small mb-1">Ghi chú</div>
                    <div class="fw-semibold">{{ $order->note ?: '---' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@if((string) $order->status === 'processing' && !$salesOrderFromWeb)
<div class="modal fade" id="autoCreateOrderModal" tabindex="-1" aria-labelledby="autoCreateOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <form method="POST" action="{{ route('admin.web-orders.convert-to-order', $order) }}" onsubmit="return confirm('Xác nhận sinh đơn hàng tự động từ đơn web này?');">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="autoCreateOrderModalLabel">Sinh đơn hàng tự động</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        Hệ thống sẽ sinh đơn hàng (Sales Order) từ đơn web đã xử lý.
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hạn giao hàng</label>
                        <input type="date" name="delivery_due_date" class="form-control" value="{{ old('delivery_due_date') }}">
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Hạn thanh toán</label>
                        <input type="date" name="payment_due_date" class="form-control" value="{{ old('payment_due_date') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check2-circle me-1"></i>Xác nhận sinh đơn hàng
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('btnSendZaloCopy');
    if (!btn) return;

    btn.addEventListener('click', async function () {
        const pdfUrl = @json(route('admin.web-orders.pdf', $order));
        const text = `Đơn web {{ $orderCode }}\nKhách hàng: {{ $order->invoice_company_name ?: $order->receiver_name }}\nTổng cộng: {{ number_format($total, 0, ',', '.') }}đ\nPDF: ${pdfUrl}`;

        try {
            await navigator.clipboard.writeText(text);
            window.open('https://chat.zalo.me/', '_blank', 'noopener');
            btn.innerHTML = 'Đã copy nội dung';
            setTimeout(() => { btn.innerHTML = '<i class="bi bi-chat-dots me-1"></i>Gửi Zalo'; }, 1800);
        } catch (e) {
            window.open('https://chat.zalo.me/', '_blank', 'noopener');
            alert('Đã mở Zalo. Bạn gửi link PDF này cho khách: ' + pdfUrl);
        }
    });
});
</script>
@endsection
