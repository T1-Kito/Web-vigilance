@extends('layouts.admin')

@section('title', 'Chi tiết báo giá')

@section('content')
@php
    $orderCode = $quote->quote_code ?? ('BG' . str_pad($quote->id, 6, '0', STR_PAD_LEFT));
    $items = $quote->items ?? collect();
    $subTotal = (float) $items->sum(fn($i) => (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0));
    $discount = (float) ($quote->discount_percent ?? 0);
    $afterDiscount = max(0, $subTotal * (1 - $discount / 100));
    $vatAmount = (float) $items->sum(function ($i) use ($discount, $quote) {
        $lineTotal = (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0);
        $lineAfterDiscount = $lineTotal * (1 - ($discount / 100));
        $lineVatRate = (float) ($i->vat_percent ?? $quote->vat_percent ?? 8);
        return $lineAfterDiscount * ($lineVatRate / 100);
    });
    $total = $afterDiscount + $vatAmount;

    $statusMap = [
        'pending' => ['label' => 'Chờ xử lý', 'class' => 'warning'],
        'approved' => ['label' => 'Đã duyệt', 'class' => 'info'],
        'lost' => ['label' => 'Không chốt', 'class' => 'secondary'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'danger'],
        'won' => ['label' => 'Đã tạo đơn bán', 'class' => 'success'],
    ];
    $status = $statusMap[$quote->status] ?? ['label' => (string) $quote->status, 'class' => 'secondary'];

    $salesOrder = $quote->convertedSalesOrder;
    $deliveries = collect();
    $invoices = collect();
    if ($salesOrder) {
        $deliveries = \App\Models\Delivery::query()->where('sales_order_id', $salesOrder->id)->orderByDesc('created_at')->get();
        $invoices = \App\Models\Invoice::query()->where('sales_order_id', $salesOrder->id)->orderByDesc('created_at')->get();
    }

    $hasDelivery = $deliveries->count() > 0;
    $hasInvoice = $invoices->count() > 0;

    $paymentTerm = (string) ($quote->payment_term ?? 'full_advance');
    $paymentTermLabel = match ($paymentTerm) {
        'debt' => 'Công nợ theo hạn',
        'deposit' => 'Đặt cọc + phần còn lại',
        default => 'Thanh toán 100% trước giao hàng',
    };

    $stepApproved = (string) $quote->status === 'approved' || (string) $quote->status === 'won' || $salesOrder;
    $stepPaymentDefined = !empty($quote->payment_term);
    $stepSalesOrder = (bool) $salesOrder;
    $stepDelivery = $hasDelivery;
    $stepInvoice = $hasInvoice;
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
            <h1 class="h4 fw-bold mb-1">Chi tiết báo giá: {{ $orderCode }}</h1>
            <div class="text-muted">Khách hàng: {{ $quote->invoice_company_name ?: $quote->receiver_name }}</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-info" id="btnSendZaloCopy">
                <i class="bi bi-chat-dots me-1"></i>Gửi Zalo
            </button>
            <a href="{{ route('admin.pdf-templates.render-default.quote', $quote) }}" class="btn btn-outline-secondary" id="btnDownloadPdfQuote">
                <i class="bi bi-download me-1"></i>Tải PDF
            </a>
            @if(($quoteTemplates ?? collect())->count() > 0)
                <div class="dropdown">
                    <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-word me-1"></i>Chọn mẫu để in
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach(($quoteTemplates ?? collect()) as $tpl)
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.document-templates.render.quote', ['documentTemplate' => $tpl, 'quote' => $quote]) }}">
                                    {{ $tpl->name }} @if($tpl->is_default)<span class="text-primary">(mặc định)</span>@endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <a href="{{ route('admin.quotes.edit', $quote) }}" class="btn btn-primary">Chỉnh sửa</a>

            @if((string) $quote->status === 'approved' && !$salesOrder)
                <button
                    type="button"
                    class="btn btn-success"
                    data-bs-toggle="modal"
                    data-bs-target="#autoCreateOrderModal"
                >
                    <i class="bi bi-magic me-1"></i>Sinh đơn hàng tự động
                </button>
            @endif


            <a href="{{ route('admin.quotes.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-3 small">
                <span class="badge {{ $stepApproved ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} px-3 py-2">1. Duyệt báo giá</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge {{ $stepPaymentDefined ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} px-3 py-2">2. Điều khoản thanh toán</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge {{ $stepSalesOrder ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} px-3 py-2">3. Sinh đơn hàng tự động</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge {{ $stepDelivery ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} px-3 py-2">4. Xuất kho</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge {{ $stepInvoice ? 'bg-success-subtle text-success' : 'bg-light text-muted' }} px-3 py-2">5. Hóa đơn</span>
            </div>
        </div>
    </div>

    @if(!empty($nameWarnings ?? []))
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold text-danger">
                <i class="bi bi-exclamation-triangle me-1"></i>Cảnh báo đối chiếu tên hàng
            </div>
            <div class="card-body">
                @foreach($nameWarnings as $warning)
                    <div class="alert alert-{{ $warning['severity'] === 'danger' ? 'danger' : 'warning' }} mb-2">
                        <div class="fw-semibold">{{ $warning['message'] }}</div>
                        <div class="small mt-1">{{ $warning['left_label'] }}: {{ $warning['left_name'] }} → {{ $warning['right_label'] }}: {{ $warning['right_name'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Thông tin khách hàng</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="text-muted small">Tên công ty</div><div class="fw-semibold">{{ $quote->invoice_company_name ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Mã số thuế</div><div class="fw-semibold">{{ $quote->customer_tax_code ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Người liên hệ</div><div class="fw-semibold">{{ $quote->customer_contact_person ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">SĐT liên hệ</div><div class="fw-semibold">{{ $quote->customer_phone ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Email</div><div class="fw-semibold">{{ $quote->customer_email ?: '---' }}</div></div>
                        <div class="col-12"><div class="text-muted small">Địa chỉ hóa đơn</div><div class="fw-semibold">{{ $quote->invoice_address ?: '---' }}</div></div>

                        <div class="col-12"><hr class="my-1"></div>
                        <div class="col-12"><div class="fw-bold">Thông tin người nhận</div></div>
                        <div class="col-md-6"><div class="text-muted small">Người nhận</div><div class="fw-semibold">{{ $quote->receiver_name ?: ($quote->customer_contact_person ?: '---') }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">SĐT người nhận</div><div class="fw-semibold">{{ $quote->receiver_phone ?: ($quote->customer_phone ?: '---') }}</div></div>
                        <div class="col-12"><div class="text-muted small">Địa chỉ giao hàng</div><div class="fw-semibold">{{ $quote->receiver_address ?: ($quote->invoice_address ?: '---') }}</div></div>
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
                            <div class="fw-semibold">{{ $paymentTerm === 'debt' ? (($quote->payment_due_days ?? 0) . ' ngày') : '---' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Tỷ lệ đặt cọc</div>
                            <div class="fw-semibold">{{ $paymentTerm === 'deposit' ? (rtrim(rtrim(number_format((float) ($quote->deposit_percent ?? 0), 2, '.', ''), '0'), '.') . '%') : '---' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Ghi chú thanh toán</div>
                            <div class="fw-semibold">{{ $quote->payment_note ?: '---' }}</div>
                        </div>
                    </div>
                    @if((string) $quote->status === 'approved' && !$salesOrder)
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Báo giá đã duyệt. Vui lòng xác nhận điều khoản thanh toán trước khi tạo đơn bán.
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Danh sách sản phẩm</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle quote-lines-table">
                            <thead>
                                <tr>
                                    <th class="ps-3">Sản phẩm</th>
                                    <th class="text-nowrap" style="width:90px;">Đơn vị</th>
                                    <th class="text-nowrap text-center" style="width:70px;">SL</th>
                                    <th class="text-nowrap text-end" style="width:140px;">Đơn giá</th>
                                    <th class="text-nowrap text-center" style="width:90px;">Thuế suất</th>
                                    <th class="text-nowrap text-end" style="width:130px;">Tiền thuế</th>
                                    <th class="text-nowrap text-end" style="width:150px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    @php
                                        $lineTotal = (float) $item->price * (int) $item->quantity;
                                        $lineVatRate = (float) ($item->vat_percent ?? $quote->vat_percent ?? 8);
                                        $lineAfterDiscount = $lineTotal * (1 - ((float) ($quote->discount_percent ?? 0) / 100));
                                        $lineVatAmount = $lineAfterDiscount * ($lineVatRate / 100);
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold product-name-wrap">{{ $item->product->name ?? ('Sản phẩm #' . $item->product_id) }}</div>
                                        </td>
                                        <td class="text-nowrap">{{ $item->unit ?: '---' }}</td>
                                        <td class="text-center">{{ (int) $item->quantity }}</td>
                                        <td class="text-end text-nowrap">{{ number_format((float) $item->price, 0, ',', '.') }}đ</td>
                                        <td class="text-center text-nowrap">{{ $lineVatRate == 0 ? 'KCT/0%' : (rtrim(rtrim(number_format($lineVatRate, 2, '.', ''), '0'), '.') . '%') }}</td>
                                        <td class="text-end text-nowrap">{{ number_format($lineVatAmount, 0, ',', '.') }}đ</td>
                                        <td class="text-end text-nowrap fw-semibold">{{ number_format($lineTotal, 0, ',', '.') }}đ</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">Không có sản phẩm.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 16px;">
                <div class="card-header bg-white fw-bold">Thông tin báo giá</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Mã báo giá</span><span class="fw-semibold">{{ $orderCode }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Ngày tạo</span><span class="fw-semibold">{{ optional($quote->created_at)->format('d/m/Y H:i') }}</span></div>
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <span class="text-muted">Trạng thái</span>
                        <div class="text-end">
                            <span class="badge bg-{{ $status['class'] }}">{{ $status['label'] }}</span>
                            @if((string) $quote->status !== 'won')
                                <form method="POST" action="{{ route('admin.quotes.update-status', $quote) }}" class="mt-2 d-flex align-items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm" style="min-width: 170px;">
                                        @foreach([
                                            'pending' => 'Chờ xử lý',
                                            'approved' => 'Đã duyệt',
                                            'lost' => 'Không chốt',
                                            'cancelled' => 'Đã hủy',
                                        ] as $key => $label)
                                            <option value="{{ $key }}" @selected((string) $quote->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Lưu</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Staff code</span><span class="fw-semibold">{{ $quote->staff_code ?: '---' }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Sales</span><span class="fw-semibold">{{ $quote->sales_name ?: '---' }}</span></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-1"><span>Tạm tính</span><strong>{{ number_format($subTotal, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span>Chiết khấu ({{ rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') }}%)</span><strong>{{ number_format($subTotal - $afterDiscount, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span>VAT</span><strong>{{ number_format($vatAmount, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between pt-2 border-top"><span class="fw-semibold">Tổng cộng</span><strong class="text-danger">{{ number_format($total, 0, ',', '.') }}đ</strong></div>
                    <hr>
                    <div class="text-muted small mb-1">Ghi chú</div>
                    <div class="fw-semibold">{{ $quote->note ?: '---' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@if((string) $quote->status === 'approved' && !$salesOrder)
<div class="modal fade" id="autoCreateOrderModal" tabindex="-1" aria-labelledby="autoCreateOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <form method="POST" action="{{ route('admin.quotes.convert-to-order', $quote) }}" onsubmit="return confirm('Xác nhận sinh đơn hàng từ báo giá đã duyệt?');">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="autoCreateOrderModalLabel">Sinh đơn hàng tự động</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 small mb-3">
                        Hệ thống sẽ tự sinh đơn hàng từ báo giá đã duyệt. Bước phát hành hóa đơn MISA thực hiện riêng tại màn chi tiết đơn bán sau khi đã xuất kho.
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
        const pdfUrl = @json(route('admin.pdf-templates.render-default.quote', $quote));
        const text = `Báo giá {{ $orderCode }}\nKhách hàng: {{ $quote->invoice_company_name ?: $quote->receiver_name }}\nTổng cộng: {{ number_format($total, 0, ',', '.') }}đ\nPDF: ${pdfUrl}`;

        try {
            window.location.href = pdfUrl;

            await navigator.clipboard.writeText(text);
            window.open('https://chat.zalo.me/', '_blank', 'noopener');
            btn.innerHTML = 'Đã tải PDF';
            setTimeout(() => { btn.innerHTML = '<i class="bi bi-chat-dots me-1"></i>Gửi Zalo'; }, 1800);
        } catch (e) {
            console.error(e);
            try {
                await navigator.clipboard.writeText(text);
            } catch (_) {}
            window.open('https://chat.zalo.me/', '_blank', 'noopener');
            alert('Đã mở Zalo và copy nội dung. Bạn có thể tải file PDF bằng nút Tải PDF bên cạnh.');
        }
    });
});
</script>
@endsection
