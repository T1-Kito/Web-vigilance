@extends('layouts.admin')

@section('title', 'Chi tiết đơn mua hàng')

@section('content')
<div class="content-card" style="padding:8px; background:#fff; border:none; box-shadow:none;">
    <div class="d-flex justify-content-end gap-2 mb-3 no-print">
        <button class="btn btn-outline-dark" onclick="window.print()">In</button>
        <a class="btn btn-secondary" href="{{ route('admin.purchase-orders.index') }}">Quay lại</a>
    </div>

    <style>
        /* Chế độ xem phiếu sạch: ẩn sidebar/header admin cho riêng trang này */
        .admin-sidebar,
        .sidebar-toggle,
        .admin-header {
            display: none !important;
        }
        .admin-main {
            margin-left: 0 !important;
            padding: 12px !important;
            background: #fff !important;
            min-height: 100vh;
        }
        body {
            background: #fff !important;
        }

        .po-paper { background:#fff; max-width:210mm; margin:0 auto; color:#111827; border:1px solid #d1d5db; padding:10px 12px; box-shadow:0 1px 4px rgba(0,0,0,0.05); }
        .po-paper .br-header { display:flex; justify-content:space-between; gap:12px; align-items:flex-end; border-bottom:1px solid #f3a6a6; padding-bottom:4px; margin-bottom:12px; }
        .po-paper .br-header-left { width:50%; flex-shrink:0; }
        .po-paper .br-header-left img { max-width:190px; height:auto; display:block; }
        .po-paper .br-header-right { width:50%; min-width:0; text-align:end; }
        .po-paper .br-header-right img { max-width:240px; height:auto; display:inline-block; margin-bottom: 20px; }
        .po-paper .br-title { text-align:center; font-weight:600; margin:20px 0 0; letter-spacing:0.45px; font-size:22px; line-height:1.15; }
        .po-paper .br-subtitle { text-align:center; font-size:1.02rem; margin:2px 0 6px; }
        .po-paper .br-muted { color:#374151; font-weight:500; }
        .po-paper .br-info { margin-top:4px; font-size:0.9rem; line-height:1.2; }
        .po-paper .po-info-panel {
            border-radius:4px;
            background:#fff;
            box-shadow:none;
            overflow:hidden;
        }
        .po-paper .po-info-two-col {
            display:grid;
            grid-template-columns:repeat(2, minmax(0, 1fr));
            align-items:stretch;
        }
        .po-paper .po-info-col { min-width:0; }
        .po-paper .po-info-col--supplier {
            padding:8px 12px 10px 12px;
            border-right:1px solid #e2e8f0;
            background:#fff;
        }
        .po-paper .po-info-col--order {
            padding:8px 12px 10px 12px;
            background:#fff;
        }
        .po-paper .po-info-col-sec {
            font-weight:600;
            font-size:0.9rem;
            letter-spacing:0.01em;
            color:#0f172a;
            margin:0 0 5px;
            padding-bottom:4px;
            line-height:1.2;
            border-bottom:1px solid #e2e8f0;
        }
        .po-paper .po-info-col .br-row + .br-row { margin-top:2px; }
        .po-paper .br-row { display:flex; gap:0; flex-wrap:nowrap; margin-bottom:0; align-items:flex-start; }
        .po-paper .po-info-col .br-row:last-child { margin-bottom:0; }
        .po-paper .br-field { width:100%; align-items:flex-start; gap:6px; min-width:0; }
        .po-paper .br-field .lbl {
            flex:0 0 168px;
            width:168px;
            max-width:168px;
            flex-shrink:0;
            font-weight:600;
            color:#1e293b;
            line-height:1.18;
            padding-top:0;
            font-size:0.88rem;
        }
        .po-paper .br-line {
            flex:1 1 auto;
            min-width:0;
            font-weight:400;
            color:#334155;
            padding:0;
            overflow-wrap:anywhere;
            word-break:break-word;
            line-height:1.18;
            font-size:0.88rem;
        }
        .po-paper .br-table { width:100%; border-collapse:collapse; margin-top:8px; table-layout:fixed; }
        .po-paper .br-table th, .po-paper .br-table td { border:1px solid #334155; padding:5px 6px; font-size:0.86rem; }
        .po-paper .br-table th { text-align:center; font-weight:700; background:#eef1f5; color:#111827; padding:6px 6px; }
        .po-paper .br-table td { vertical-align:middle; }
        .po-paper .br-table tbody td { word-wrap:break-word; }
        .po-paper .br-table tfoot td { font-weight:700; background:#fafbfc; padding:5px 6px; }
        .po-paper .br-sign { display:grid; grid-template-columns:repeat(4, 1fr); gap:6px 12px; margin-top:10px; padding-top:2px; }
        .po-paper .br-sign .sig-col { text-align:center; }
        .po-paper .br-sign .cap { font-weight:700; font-size:0.86rem; color:#1e293b; }
        .po-paper .br-sign .sig-line { margin-top:28px; min-height:1.2em; font-weight:600; font-size:0.86rem; color:#0f172a; }
        .po-paper .br-footer {
            margin-top: 10px;
            border-top: 1px solid #f3a6a6;
            padding-top: 6px;
            font-size: 0.78rem;
            color: #4b5563;
            line-height: 1.28;
        }
        .po-paper .br-footer .company {
            font-weight: 700;
            color: #dc2626;
            font-size: 0.92rem;
            margin-bottom: 3px;
        }
        .print-fixed-header,
        .print-fixed-footer { display:none !important; }

        @media print {
            html, body { background:#fff !important; }
            .no-print,
            .admin-sidebar,
            .admin-header,
            .sidebar-toggle { display:none !important; }
            .admin-main { margin:0 !important; padding:0 !important; }
            .content-card { padding:0 !important; border:none !important; box-shadow:none !important; }
            .po-print-area { position: relative !important; min-height:auto !important; background:#fff !important; padding:0 !important; }
            .po-paper { border:none !important; box-shadow:none !important; position:relative !important; min-height:calc(297mm - 16mm) !important; padding:0 0 24mm 0 !important; max-width:none !important; }
            .po-paper .br-header { gap:8px !important; border-bottom:1px solid #f3a6a6 !important; padding-bottom:3px !important; margin-bottom:10px !important; }
            .po-paper .br-header-left img { max-width:165px !important; }
            .po-paper .br-header-right img { max-width:165px !important; }
            .po-paper .br-title { margin:20px 0 0 !important; font-size:24px !important; }
            .po-paper .br-subtitle { margin:1px 0 4px !important; font-size:0.95rem !important; }
            .po-paper .br-info { margin-top:0 !important; }
            .po-paper .po-info-col--supplier,
            .po-paper .po-info-col--order { padding:5px 8px 7px 8px !important; }
            .po-paper .po-info-col .br-row + .br-row { margin-top:1px !important; }
            .po-paper .br-field .lbl { flex:0 0 120px !important; width:120px !important; max-width:120px !important; font-size:0.78rem !important; }
            .po-paper .br-line { font-size:0.8rem !important; }
            .po-paper .br-table { margin-top:4px !important; }
            .po-paper .br-table th, .po-paper .br-table td { padding:3px 4px !important; font-size:0.78rem !important; }
            .po-paper .br-sign { margin-top:8px !important; }
            .po-paper .br-footer { position:absolute !important; left:0 !important; right:0 !important; bottom:0 !important; margin-top:0 !important; border-top:1px solid #f3a6a6 !important; padding-top:6px !important; font-size:0.78rem !important; line-height:1.28 !important; color:#4b5563 !important; }
            @page { size: A4 portrait; margin: 8mm; }
        }
    </style>

    <div class="po-print-area">

        <div class="po-paper">
            <div class="br-header">
                <div class="br-header-left">
                    <img src="{{ asset('logo1.png') }}" alt="Logo trái">
                </div>
                <div class="br-header-right">
                    <img src="{{ asset('logo2.png') }}" alt="Logo phải">
                </div>
            </div>

            <div class="br-title">PHIẾU MUA HÀNG</div>
            <div class="br-subtitle" style="margin-bottom:6px;">(Số: <span class="br-muted">{{ $order->po_number ?: $order->code }}</span>)</div>

            <div class="br-info">
                <div class="po-info-panel">
                    <div class="po-info-two-col">
                    <div class="po-info-col po-info-col--supplier">
                        <div class="po-info-col-sec">Kính gửi nhà cung cấp</div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Mã số thuế:</span>
                                <span class="br-line">{{ $order->supplier_tax_code ?: '' }}</span>
                            </div>
                        </div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Tên nhà cung cấp:</span>
                                <span class="br-line">{{ $order->supplier_name ?: '' }}</span>
                            </div>
                        </div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Địa chỉ:</span>
                                <span class="br-line">{{ $order->supplier_address ?: '' }}</span>
                            </div>
                        </div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Người liên hệ:</span>
                                <span class="br-line">{{ $order->supplier_contact_name ?: '' }}</span>
                            </div>
                        </div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Số điện thoại:</span>
                                <span class="br-line">{{ $order->supplier_contact_phone ?: '' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="po-info-col po-info-col--order">
                        <div class="po-info-col-sec">Thông tin đơn hàng</div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Ngày lập phiếu:</span>
                                <span class="br-line">{{ now()->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Ngày giao hàng:</span>
                                <span class="br-line">{{ optional($order->delivery_date)->format('d/m/Y') ?: '' }}</span>
                            </div>
                        </div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Giao tại:</span>
                                <span class="br-line">{{ $order->delivery_location ?: '' }}</span>
                            </div>
                        </div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Nhân viên mua hàng:</span>
                                <span class="br-line">{{ $order->buyer_name ?: '' }}</span>
                            </div>
                        </div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Chức vụ:</span>
                                <span class="br-line">{{ $order->buyer_position ?: '' }}</span>
                            </div>
                        </div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Số ngày được nợ:</span>
                                <span class="br-line">{{ (string) (int) ($order->credit_days ?? 0) }}</span>
                            </div>
                        </div>
                        <div class="br-row">
                            <div class="br-field">
                                <span class="lbl">Loại tiền thanh toán:</span>
                                <span class="br-line">{{ $order->payment_currency ?: 'VND' }}</span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            @php
                $poDisplayItems = $order->items->values()->filter(function ($it) {
                    $name = trim((string) ($it->item_name ?? ''));
                    $serial = trim((string) ($it->serial_number ?? ''));
                    $qty = (float) ($it->quantity ?? 0);
                    return $name !== '' || $serial !== '' || $qty > 0;
                });
                $poItemsTotal = (float) $poDisplayItems->sum('amount');
            @endphp
            <table class="br-table">
                <thead>
                    <tr>
                        <th style="width:5%;">STT</th>
                        <th style="width:12%;">Số seri</th>
                        <th style="width:27%;">Tên hàng</th>
                        <th style="width:8%;">Đơn vị tính</th>
                        <th style="width:6%;">SL</th>
                        <th style="width:10%;">Giá trị</th>
                        <th style="width:11%;">Thời gian bảo hành</th>
                        <th style="width:8%;">Thuế GTGT</th>
                        <th style="width:13%;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($poDisplayItems as $it)
                    @php
                        $lineAmount = (float) ($it->amount ?? 0);
                        $lineTaxPercent = (float) ($it->tax_percent ?? 0);
                        $lineTotal = $lineAmount + ($lineAmount * $lineTaxPercent / 100);
                    @endphp
                    <tr>
                        <td style="text-align:center; font-weight:700;">{{ $loop->iteration }}</td>
                        <td>{{ $it->serial_number ?: '' }}</td>
                        <td>{{ $it->item_name ?: '' }}</td>
                        <td style="text-align:center;">{{ $it->unit ?: '' }}</td>
                        <td style="text-align:center;">{{ rtrim(rtrim(number_format((float)$it->quantity, 2, '.', ''), '0'), '.') }}</td>
                        <td style="text-align:right;">{{ number_format($lineAmount, 0, ',', '.') }}</td>
                        <td style="text-align:center;">{{ $it->warranty_period ?: '' }}</td>
                        <td style="text-align:center;">{{ rtrim(rtrim(number_format($lineTaxPercent, 2, '.', ''), '0'), '.') }}%</td>
                        <td style="text-align:right;">{{ number_format($lineTotal, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center; color:#6b7280;">Chưa có dòng sản phẩm</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8" style="text-align:right; padding-right:8px;">Tổng cộng:</td>
                        <td style="text-align:right;">
                            {{ number_format($poDisplayItems->sum(function ($it) { $amount = (float) ($it->amount ?? 0); $tax = (float) ($it->tax_percent ?? 0); return $amount + ($amount * $tax / 100); }), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>

            <div class="br-sign">
                <div class="sig-col">
                    <div class="cap">Người lập biểu</div>
                    <div class="sig-line"></div>
                </div>
                <div class="sig-col">
                    <div class="cap">Người giao hàng</div>
                    <div class="sig-line"></div>
                </div>
                <div class="sig-col">
                    <div class="cap">Thủ kho</div>
                    <div class="sig-line"></div>
                </div>
                <div class="sig-col">
                    <div class="cap">Giám đốc</div>
                    <div class="sig-line"></div>
                </div>
            </div>

            <div class="br-footer">
                <div class="company">CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</div>
                <div>Địa chỉ: Phòng B15.09 Tầng 15, Tháp B Tòa nhà Rivergate, 151-155 Bến Vân Đồn, Phường Khánh Hội, TP.HCM</div>
                <div>MST: 0318231312 | Hotline: 02887617015 | Email: vigilancevn@gmail.com | Website: https://vigilance.com.vn</div>
            </div>
        </div>
    </div>
</div>
@endsection
