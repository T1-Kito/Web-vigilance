@extends('layouts.admin')

@section('title', 'Chi tiết hóa đơn')

@section('content')
@php
    $order = $invoice->order;
    $salesOrder = $invoice->salesOrder;
    $misaResult = session('misa_invoice_result', []);
    $misaVerify = session('misa_verify_result', []);
    $misaPreviewUrl = (string) (session('misa_preview_url') ?: data_get($misaVerify, 'best.json.data', ''));

    $misaCodeDisplay = (string) ($invoice->misa_invoice_code ?? '');
    $misaTxnDisplay = (string) ($invoice->misa_transaction_id ?? '');
    if ($misaCodeDisplay === '' || $misaTxnDisplay === '') {
        $resp = $invoice->misa_response_payload;
        if (is_string($resp)) {
            $decodedResp = json_decode($resp, true);
            $resp = is_array($decodedResp) ? $decodedResp : [];
        }
        if (is_array($resp)) {
            $pubRaw = data_get($resp, 'publishInvoiceResult', data_get($resp, 'PublishInvoiceResult', []));
            if (is_string($pubRaw)) {
                $decodedPub = json_decode($pubRaw, true);
                $pubRaw = is_array($decodedPub) ? $decodedPub : [];
            }
            $pub = is_array($pubRaw) && array_is_list($pubRaw) ? ($pubRaw[0] ?? []) : (is_array($pubRaw) ? $pubRaw : []);
            if ($misaCodeDisplay === '') {
                $misaCodeDisplay = (string) (data_get($pub, 'InvNo') ?: data_get($pub, 'InvoiceCode', ''));
            }
            if ($misaTxnDisplay === '') {
                $misaTxnDisplay = (string) (data_get($pub, 'TransactionID', ''));
            }
        }
    }
@endphp

<style>
    .inv-page {
        --bg: #f4f7fb;
        --card: #ffffff;
        --txt: #0f172a;
        --muted: #64748b;
        --line: #e6ebf2;
        --brand: #2563eb;
    }

    .inv-wrap {
        background: var(--bg);
        border-radius: 20px;
        padding: 16px;
    }

    .inv-top {
        position: sticky;
        top: 8px;
        z-index: 11;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        padding: 14px 16px;
        border-radius: 14px;
        background: rgba(255,255,255,0.92);
        backdrop-filter: blur(6px);
        box-shadow: 0 8px 22px rgba(15,23,42,.08);
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .inv-headline h1 {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--txt);
    }

    .inv-headline .sub {
        color: var(--muted);
        font-size: .9rem;
        margin-top: 3px;
    }

    .inv-card {
        background: var(--card);
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 4px 14px rgba(15,23,42,.04);
    }

    .inv-card + .inv-card { margin-top: 12px; }

    .inv-title {
        font-weight: 800;
        color: var(--txt);
        margin-bottom: 10px;
        font-size: 1rem;
    }

    .kv {
        display: grid;
        grid-template-columns: 160px 1fr;
        gap: 8px;
        margin-bottom: 8px;
        font-size: .92rem;
    }
    .kv .k { color: var(--muted); }
    .kv .v { color: var(--txt); font-weight: 700; }

    .soft-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
    }
    .soft-table thead th {
        background: #f8fafc;
        font-size: .84rem;
        padding: 10px 12px;
        color: #334155;
    }
    .soft-table tbody td {
        border-top: 1px solid var(--line);
        padding: 11px 12px;
        font-size: .92rem;
    }

    .misa-preview {
        width: 100%;
        min-height: 640px;
        border: 1px solid var(--line);
        border-radius: 12px;
        background: #fff;
    }

    .json-box {
        background: #f8fafc;
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 10px;
        font-size: .82rem;
        max-height: 260px;
        overflow: auto;
        white-space: pre-wrap;
        word-break: break-word;
    }

    @media (max-width: 992px) {
        .kv { grid-template-columns: 1fr; gap: 2px; }
        .misa-preview { min-height: 520px; }
    }
</style>

<div class="container-fluid py-3 inv-page">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="inv-wrap">
        <div class="inv-top">
            <div class="inv-headline">
                <h1>Hóa đơn: {{ $invoice->invoice_code }}</h1>
                <div class="sub">Đơn nguồn: {{ $order->order_code ?? $salesOrder->sales_order_code ?? ('#' . ($invoice->order_id ?? $invoice->sales_order_id)) }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('admin.invoices.verify-misa', $invoice) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-success btn-sm">Đối soát MISA</button>
                </form>
                @if(!empty($misaPreviewUrl))
                    <a href="{{ $misaPreviewUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-sm">Mở bản MISA</a>
                @endif
                @if($salesOrder)
                    <a href="{{ route('admin.sales-orders.show', $salesOrder) }}" class="btn btn-outline-primary btn-sm">Về đơn bán</a>
                @elseif($order)
                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">Về đơn hàng</a>
                @endif
                <a href="{{ route('admin.invoices.index') }}" class="btn btn-light border btn-sm">Danh sách</a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                @if(!empty($misaPreviewUrl))
                <div class="inv-card">
                    <div class="inv-title">Mẫu hóa đơn từ MISA (preview trực tiếp)</div>
                    <iframe class="misa-preview" src="{{ $misaPreviewUrl }}"></iframe>
                </div>
                @endif

                <div class="inv-card">
                    <div class="inv-title">Dòng hóa đơn</div>
                    <div class="table-responsive">
                        <table class="soft-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th style="width:120px;">Đơn vị</th>
                                    <th style="width:100px;">SL</th>
                                    <th style="width:150px;">Đơn giá</th>
                                    <th style="width:160px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($invoice->items as $line)
                                <tr>
                                    <td>{{ $line->product->name ?? ('Sản phẩm #' . $line->product_id) }}</td>
                                    <td>{{ $line->unit ?: '---' }}</td>
                                    <td>{{ (int) $line->quantity }}</td>
                                    <td>{{ number_format((float) $line->unit_price, 0, ',', '.') }}đ</td>
                                    <td><strong>{{ number_format((float) $line->line_total, 0, ',', '.') }}đ</strong></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="inv-card" style="position: sticky; top: 84px;">
                    <div class="inv-title">Thông tin chứng từ</div>

                    <div class="kv"><div class="k">Mã hóa đơn</div><div class="v">{{ trim(($invoice->misa_inv_series ?: '') . ' ' . ($invoice->invoice_code ?: '')) ?: '---' }}</div></div>
                    <div class="kv"><div class="k">Ngày phát hành</div><div class="v">{{ optional($invoice->issued_at)->format('d/m/Y H:i') }}</div></div>
                    <div class="kv"><div class="k">Trạng thái</div><div class="v">
                        <span class="badge bg-{{ $invoice->status === 'issued' ? 'success' : ($invoice->status === 'cancelled' ? 'danger' : 'secondary') }}">
                            {{ $invoice->status === 'issued' ? 'Đã phát hành' : ($invoice->status === 'cancelled' ? 'Đã hủy' : 'Nháp') }}
                        </span>
                    </div></div>

                    <hr>

                    <div class="kv"><div class="k">RefID</div><div class="v">{{ $invoice->misa_ref_id ?: '---' }}</div></div>
                    <div class="kv"><div class="k">TransactionID</div><div class="v">{{ $misaTxnDisplay ?: '---' }}</div></div>
                    <div class="kv"><div class="k">InvSeries</div><div class="v">{{ $invoice->misa_inv_series ?: '---' }}</div></div>
                    <div class="kv"><div class="k">Mã hóa đơn MISA</div><div class="v">{{ $misaCodeDisplay ?: '---' }}</div></div>
                    <div class="kv"><div class="k">MISA issued_at</div><div class="v">{{ optional($invoice->misa_issued_at)->format('d/m/Y H:i') ?: '---' }}</div></div>

                    @if(!empty($invoice->misa_error_message))
                    <div class="alert alert-warning mt-2 mb-0 small">{{ $invoice->misa_error_message }}</div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between mb-1"><span>Tạm tính</span><strong>{{ number_format((float) $invoice->sub_total, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span>Chiết khấu</span><strong>{{ number_format((float) $invoice->discount_percent, 2, ',', '.') }}%</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span>VAT</span><strong>{{ number_format((float) $invoice->vat_percent, 2, ',', '.') }}%</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span>Tiền VAT</span><strong>{{ number_format((float) $invoice->vat_amount, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between pt-2 border-top"><span><strong>Tổng cộng</strong></span><strong class="text-danger">{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}đ</strong></div>

                    <hr>
                    <div class="small text-muted mb-2">Timeline MISA</div>
                    @php
                        $misaTimeline = [];

                        if (!empty($invoice->misa_ref_id) || !empty($invoice->misa_transaction_id)) {
                            $misaTimeline[] = [
                                'time' => optional($invoice->created_at)->format('d/m/Y H:i:s') ?: '---',
                                'label' => 'Đồng bộ dữ liệu MISA',
                                'desc' => 'RefID: ' . ($invoice->misa_ref_id ?: '---') . ' | Txn: ' . ($misaTxnDisplay ?: '---'),
                            ];
                        }

                        if (($invoice->status ?? '') === 'issued') {
                            $misaTimeline[] = [
                                'time' => optional($invoice->issued_at)->format('d/m/Y H:i:s') ?: '---',
                                'label' => 'Phát hành hóa đơn',
                                'desc' => 'Mã HĐ: ' . ($misaCodeDisplay ?: $invoice->invoice_code),
                            ];
                        } else {
                            $misaTimeline[] = [
                                'time' => optional($invoice->issued_at)->format('d/m/Y H:i:s') ?: '---',
                                'label' => 'Tạo hóa đơn nháp',
                                'desc' => 'Hóa đơn đang ở trạng thái nháp nội bộ.',
                            ];
                        }

                        if (!empty($misaVerify)) {
                            $misaTimeline[] = [
                                'time' => now()->format('d/m/Y H:i:s'),
                                'label' => 'Đối soát trạng thái MISA',
                                'desc' => 'HTTP: ' . (data_get($misaVerify, 'best.status', '---')) . ' | Successful: ' . (data_get($misaVerify, 'best.successful', false) ? 'Yes' : 'No'),
                            ];
                        }
                    @endphp

                    <div class="timeline small">
                        @foreach($misaTimeline as $event)
                            <div class="mb-2 pb-2 border-bottom">
                                <div class="fw-semibold">{{ $event['label'] }}</div>
                                <div class="text-muted">{{ $event['time'] }}</div>
                                <div>{{ $event['desc'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($misaVerify))
                        <div class="small text-muted mb-1">Kết quả đối soát gần nhất</div>
                        <div class="json-box">{{ json_encode($misaVerify['best'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
