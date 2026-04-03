<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu mua hàng</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color:#111827; margin:0; font-size:11.5px; font-weight:400; }
        
        .po-paper { width:100%; background:#fff; padding:7mm 8mm; }
        .br-header { width:100%; border-collapse:collapse; margin-bottom: 5px; }
        .br-header td { vertical-align: top; }
        .br-header-left { width:30%; }
        .br-header-left img { max-width:138px; height:auto; }
        .br-header-right { width:70%; text-align:right; font-size:11.3px; line-height:1.28; font-weight:400; }
        .br-company-name { font-weight:700; color:#ef4444; font-size:12.2px; }
        .br-title { text-align:center; font-weight:700; font-size:17px; margin:6px 0 1px; letter-spacing: .2px; }
        .br-subtitle { text-align:center; font-size:11.8px; margin:0 0 7px; font-weight:400; }
        .br-muted { color:#374151; }

        .po-info-table { width:100%; border-collapse:collapse; margin-bottom:8px; }
        .po-info-table td { width:50%; vertical-align:top; padding:3px 5px; border:1px solid #cfd6df; }
        .po-info-col-sec { font-weight:700; font-size:11.2px; margin-bottom:1px; border-bottom:1px solid #e2e8f0; padding-bottom:1px; line-height:1.05; }
        .po-info-lines { width:100%; border-collapse:collapse; margin:0; table-layout:auto; }
        .po-info-lines tr { height:auto; }
        .po-info-lines td { border:none; padding:0; font-size:10.4px; line-height:0.98; vertical-align:top; }
        .po-info-lines .lbl { width:1%; font-weight:700; color:#0f172a; white-space:nowrap; padding-right:2px; }
        .po-info-lines .val { font-weight:400; word-break:break-word; }

        .br-table { width:100%; border-collapse:collapse; table-layout:fixed; }
        .br-table th, .br-table td { border:1px solid #334155; padding:3.5px 4px; font-size:10.8px; font-weight: 400; }
        .br-table th { text-align:center; background:#eef1f5; font-weight:700; }
        .br-table td { vertical-align:middle; word-break:break-word; }
        .text-center { text-align:center; }
        .text-right { text-align:right; }

        .br-sign { margin-top:12px; width:100%; border-collapse:collapse; }
        .br-sign td { width:25%; text-align:center; font-size:12px; vertical-align:top; }
        .cap { font-weight:700; }
        .sig-line { margin-top:26px; min-height:14px; }
    </style>
</head>
<body>
<div class="po-paper">
    <table class="br-header">
        <tr>
            <td class="br-header-left">
                <img src="{{ public_path('logovigilance.jpg') }}" alt="Vigilance">
            </td>
            <td class="br-header-right">
                <div class="br-company-name">CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</div>
                <div>Địa chỉ: Phòng B15.09 Tầng 15, Tháp B Tòa nhà Rivergate</div>
                <div>151-155 Bến Vân Đồn, Phường Khánh Hội, TP.HCM</div>
                <div>Mã số thuế: 0318231312</div>
                <div>Email : vigilancevn@gmail.com</div>
            </td>
        </tr>
    </table>

    <div class="br-title">PHIẾU MUA HÀNG</div>
    <div class="br-subtitle">(Số: <span class="br-muted">{{ $order->po_number ?: $order->code }}</span>)</div>

    <table class="po-info-table">
        <tr>
            <td>
                <div class="po-info-col-sec">Kính gửi nhà cung cấp</div>
                <table class="po-info-lines">
                    <tr><td class="lbl">Mã số thuế:</td><td class="val">{{ $order->supplier_tax_code ?: '' }}</td></tr>
                    <tr><td class="lbl">Tên nhà cung cấp:</td><td class="val">{{ $order->supplier_name ?: '' }}</td></tr>
                    <tr><td class="lbl">Địa chỉ:</td><td class="val">{{ preg_replace('/\s+/u', ' ', (string) ($order->supplier_address ?: '')) }}</td></tr>
                    <tr><td class="lbl">Người liên hệ:</td><td class="val">{{ $order->supplier_contact_name ?: '' }}</td></tr>
                    <tr><td class="lbl">Số điện thoại:</td><td class="val">{{ $order->supplier_contact_phone ?: '' }}</td></tr>
                </table>
            </td>
            <td>
                <div class="po-info-col-sec">Thông tin đơn hàng</div>
                <table class="po-info-lines">
                    <tr><td class="lbl">Ngày lập phiếu:</td><td class="val">{{ now()->format('d/m/Y') }}</td></tr>
                    <tr><td class="lbl">Ngày giao hàng:</td><td class="val">{{ optional($order->delivery_date)->format('d/m/Y') ?: '' }}</td></tr>
                    <tr><td class="lbl">Giao tại:</td><td class="val">{{ preg_replace('/\s+/u', ' ', (string) ($order->delivery_location ?: '')) }}</td></tr>
                    <tr><td class="lbl">Nhân viên mua hàng:</td><td class="val">{{ $order->buyer_name ?: '' }}</td></tr>
                    <tr><td class="lbl">Chức vụ:</td><td class="val">{{ $order->buyer_position ?: '' }}</td></tr>
                    <tr><td class="lbl">Số ngày được nợ:</td><td class="val">{{ (string) (int) ($order->credit_days ?? 0) }}</td></tr>
                    <tr><td class="lbl">Loại tiền thanh toán:</td><td class="val">{{ $order->payment_currency ?: 'VND' }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    @php
        $poDisplayItems = $order->items->values()->filter(function ($it) {
            $name = trim((string) ($it->item_name ?? ''));
            $serial = trim((string) ($it->serial_number ?? ''));
            $qty = (float) ($it->quantity ?? 0);
            return $name !== '' || $serial !== '' || $qty > 0;
        });
    @endphp

    <table class="br-table">
        <thead>
        <tr>
            <th style="width:5%;">STT</th>
            <th style="width:12%;">Số seri</th>
            <th style="width:24%;">Tên hàng</th>
            <th style="width:8%;">Đơn vị tính</th>
            <th style="width:6%;">SL</th>
            <th style="width:11%;">Giá trị</th>
            <th style="width:11%;">Thời gian bảo hành</th>
            <th style="width:8%;">Thuế GTGT</th>
            <th style="width:15%;">Thành tiền</th>
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
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $it->serial_number ?: '' }}</td>
                <td>{{ $it->item_name ?: '' }}</td>
                <td class="text-center">{{ $it->unit ?: '' }}</td>
                <td class="text-center">{{ rtrim(rtrim(number_format((float)$it->quantity, 2, '.', ''), '0'), '.') }}</td>
                <td class="text-right">{{ number_format($lineAmount, 0, ',', '.') }}</td>
                <td class="text-center">{{ $it->warranty_period ?: '' }}</td>
                <td class="text-center">{{ rtrim(rtrim(number_format($lineTaxPercent, 2, '.', ''), '0'), '.') }}%</td>
                <td class="text-right">{{ number_format($lineTotal, 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center">Chưa có dòng sản phẩm</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot>
        <tr>
            <td colspan="8" class="text-right"><strong>Tổng cộng:</strong></td>
            <td class="text-right"><strong>{{ number_format($poDisplayItems->sum(function ($it) { $amount = (float) ($it->amount ?? 0); $tax = (float) ($it->tax_percent ?? 0); return $amount + ($amount * $tax / 100); }), 0, ',', '.') }}</strong></td>
        </tr>
        </tfoot>
    </table>

    <table class="br-sign">
        <tr>
            <td><div class="cap">Người lập biểu</div><div class="sig-line"></div></td>
            <td><div class="cap">Người giao hàng</div><div class="sig-line"></div></td>
            <td><div class="cap">Thủ kho</div><div class="sig-line"></div></td>
            <td><div class="cap">Giám đốc</div><div class="sig-line"></div></td>
        </tr>
    </table>
</div>
</body>
</html>
