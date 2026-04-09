@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng')

@section('content')
@php
    $total = (float) $salesOrder->items->sum(fn($i) => (float) ($i->unit_price ?? 0) * (int) ($i->quantity ?? 0));
    $completionRate = $totalOrdered > 0 ? min(100, round(($totalDelivered / $totalOrdered) * 100)) : 0;
@endphp

<div class="container-fluid py-4">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Số Đơn hàng: {{ $salesOrder->sales_order_code }}</h1>
            <div class="text-muted">Nguồn báo giá: {{ $salesOrder->quote->quote_code ?? '---' }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales-orders.pdf', $salesOrder) }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-pdf me-1"></i>PDF đơn hàng
            </a>
            <a href="{{ route('admin.document-templates.render-default.sales-order', $salesOrder) }}" class="btn btn-success">
                <i class="bi bi-printer me-1"></i>In nhanh (mẫu mặc định)
            </a>
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-word me-1"></i>Chọn mẫu để in
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @forelse(($salesOrderTemplates ?? collect()) as $tpl)
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.document-templates.render.sales-order', ['documentTemplate' => $tpl, 'salesOrder' => $salesOrder]) }}">
                                {{ $tpl->name }} @if($tpl->is_default)<span class="text-primary">(mặc định)</span>@endif
                            </a>
                        </li>
                    @empty
                        <li><span class="dropdown-item-text text-muted">Chưa có mẫu đơn hàng</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.document-templates.index', ['type' => 'sales_order']) }}">
                                Vào quản lý mẫu in (màn chung)
                            </a>
                        </li>
                    @endforelse
                </ul>
            </div>
            <a href="{{ route('admin.sales-orders.index') }}" class="btn btn-outline-secondary">Quay lại danh sách</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="small text-muted">Giá trị đơn hàng</div>
                            <div class="h5 mb-0 text-danger fw-bold">{{ number_format($total, 0, ',', '.') }}đ</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="small text-muted">Tiến độ giao</div>
                            <div class="h5 mb-0 fw-bold">{{ $totalDelivered }}/{{ $totalOrdered }} ({{ $completionRate }}%)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <div class="small text-muted">Số chứng từ</div>
                            <div class="h5 mb-0 fw-bold">PX: {{ $deliveries->count() }} | HĐ: {{ $invoices->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Thông tin đơn hàng</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="text-muted small">Khách hàng xuất hóa đơn</div><div class="fw-semibold">{{ $salesOrder->invoice_company_name ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Mã số thuế</div><div class="fw-semibold">{{ $salesOrder->customer_tax_code ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Người liên hệ</div><div class="fw-semibold">{{ $salesOrder->customer_contact_person ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">SĐT / Email liên hệ</div><div class="fw-semibold">{{ $salesOrder->customer_phone ?: '---' }} @if($salesOrder->customer_email) / {{ $salesOrder->customer_email }} @endif</div></div>
                        <div class="col-12"><div class="text-muted small">Địa chỉ hóa đơn</div><div class="fw-semibold">{{ $salesOrder->invoice_address ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Người nhận hàng</div><div class="fw-semibold">{{ $salesOrder->receiver_name ?: '---' }} ({{ $salesOrder->receiver_phone ?: '---' }})</div></div>
                        <div class="col-md-6"><div class="text-muted small">Địa chỉ giao hàng</div><div class="fw-semibold">{{ $salesOrder->receiver_address ?: '---' }}</div></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Sản phẩm đơn hàng</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3">Tên sản phẩm</th>
                                    <th style="width:120px;">Đơn vị</th>
                                    <th style="width:110px;">SL</th>
                                    <th style="width:150px;">Đơn giá</th>
                                    <th style="width:160px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($salesOrder->items as $item)
                                <tr>
                                    <td class="ps-3">{{ $item->product->name ?? ('SP #' . $item->product_id) }}</td>
                                    <td>{{ $item->unit ?: '---' }}</td>
                                    <td>{{ (int) $item->quantity }}</td>
                                    <td>{{ number_format((float) $item->unit_price, 0, ',', '.') }}đ</td>
                                    <td class="fw-semibold">{{ number_format((float) $item->unit_price * (int) $item->quantity, 0, ',', '.') }}đ</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Lịch sử phiếu xuất kho</div>
                <div class="card-body">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">Tiến độ giao hàng: {{ $totalDelivered }}/{{ $totalOrdered }} ({{ $completionRate }}%)</div>
                        <a href="{{ route('admin.sales-orders.deliveries.create', $salesOrder) }}" class="btn btn-sm btn-primary">Tạo phiếu xuất kho</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Ngày xuất</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Xem</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($deliveries as $d)
                                <tr>
                                    <td>{{ $d->delivery_code }}</td>
                                    <td>{{ optional($d->delivered_at)->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge bg-{{ $d->status === 'confirmed' ? 'success' : 'secondary' }}">{{ $d->status }}</span></td>
                                    <td class="text-end"><a href="{{ route('admin.deliveries.show', $d) }}" class="btn btn-sm btn-outline-secondary">Chi tiết</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Chưa có phiếu xuất kho.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Lịch sử hóa đơn</div>
                <div class="card-body">
                    <div class="mb-3 text-end">
                        <a href="{{ route('admin.sales-orders.invoices.create', $salesOrder) }}" class="btn btn-sm btn-primary">Phát hành hóa đơn</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Mã hóa đơn</th>
                                    <th>Ngày phát hành</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Xem</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($invoices as $inv)
                                <tr>
                                    <td>{{ $inv->invoice_code }}</td>
                                    <td>{{ optional($inv->issued_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ number_format((float) $inv->total_amount, 0, ',', '.') }}đ</td>
                                    <td><span class="badge bg-{{ $inv->status === 'issued' ? 'success' : 'secondary' }}">{{ $inv->status }}</span></td>
                                    <td class="text-end"><a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-sm btn-outline-secondary">Chi tiết</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Chưa có hóa đơn.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div style="position: sticky; top: 16px;">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-bold">Thông tin xử lý đơn hàng</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Số đơn hàng</span><span class="fw-semibold">{{ $salesOrder->sales_order_code }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Ngày tạo</span><span class="fw-semibold">{{ optional($salesOrder->created_at)->format('d/m/Y H:i') }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Hạn giao hàng</span><span class="fw-semibold">{{ optional($salesOrder->delivery_due_date)->format('d/m/Y') ?: '---' }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Hạn thanh toán</span><span class="fw-semibold">{{ optional($salesOrder->payment_due_date)->format('d/m/Y') ?: '---' }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Trạng thái đơn</span><span class="badge bg-secondary">{{ $salesOrder->status }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Hình thức TT</span><span class="fw-semibold text-end ms-2">
                            @if(($salesOrder->payment_term ?? 'full_advance') === 'debt')
                                Công nợ {{ (int) ($salesOrder->payment_due_days ?? 0) }} ngày
                            @elseif(($salesOrder->payment_term ?? 'full_advance') === 'deposit')
                                Đặt cọc {{ (float) ($salesOrder->deposit_percent ?? 0) }}%
                            @else
                                Thanh toán 100%
                            @endif
                        </span></div>
                        @php
                            $payMap = ['unpaid' => 'Chưa thanh toán', 'partial' => 'Thanh toán một phần', 'paid' => 'Đã thanh toán', 'overdue' => 'Quá hạn'];
                            $payLabel = $payMap[$salesOrder->payment_status ?? 'unpaid'] ?? ($salesOrder->payment_status ?? '---');
                            $payBadge = ($salesOrder->payment_status ?? 'unpaid') === 'paid' ? 'success' : ((($salesOrder->payment_status ?? 'unpaid') === 'partial') ? 'warning' : ((($salesOrder->payment_status ?? 'unpaid') === 'overdue') ? 'danger' : 'secondary'));
                        @endphp
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Trạng thái công nợ</span><span class="badge bg-{{ $payBadge }}">{{ $payLabel }}</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Đã thanh toán</span><span class="fw-semibold text-success">{{ number_format((float) ($paidAmount ?? 0), 0, ',', '.') }}đ</span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-muted">Còn phải thu</span><span class="fw-semibold text-danger">{{ number_format((float) ($remainingDebt ?? 0), 0, ',', '.') }}đ</span></div>
                        @if(!empty($salesOrder->payment_due_date))
                            <div class="d-flex justify-content-between mb-2"><span class="text-muted">Hạn thanh toán</span><span class="fw-semibold">{{ optional($salesOrder->payment_due_date)->format('d/m/Y') }}</span></div>
                        @endif
                        @if(!empty($salesOrder->payment_note))
                            <div class="small text-muted mb-2">Ghi chú TT: {{ $salesOrder->payment_note }}</div>
                        @endif
                        <div class="d-flex justify-content-between pt-2 border-top"><span class="fw-semibold">Tổng tiền</span><span class="fw-bold text-danger">{{ number_format($total, 0, ',', '.') }}đ</span></div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Xem chứng từ</div>
                    <div class="card-body d-grid gap-2">
                        @if($salesOrder->quote)
                            <a href="{{ route('orders.quote', ['orderCode' => $salesOrder->quote->quote_code]) }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-file-earmark-text me-1"></i>Xem form báo giá (bản khách)
                            </a>
                        @endif

                        @if($salesOrder->quote)
                            <a href="{{ route('admin.quotes.show', $salesOrder->quote) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-window me-1"></i>Xem chi tiết báo giá (admin)
                            </a>
                        @endif

                        <a href="{{ route('admin.sales-orders.deliveries.create', $salesOrder) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-truck me-1"></i>Tạo phiếu xuất kho
                        </a>
                        <a href="{{ route('admin.sales-orders.invoices.create', $salesOrder) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-receipt me-1"></i>Phát hành hóa đơn
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
