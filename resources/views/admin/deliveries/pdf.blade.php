<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu xuất kho bán hàng</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color:#111827; margin:0; font-size:11.5px; }

        .paper { width:100%; background:#fff; padding:7mm 8mm; }
        .br-header { width:100%; border-collapse:collapse; margin-bottom: 6px; }
        .br-header td { vertical-align: top; }
        .br-header-left { width:30%; }
        .br-header-left img { max-width:138px; height:auto; }
        .br-header-right { width:70%; text-align:right; font-size:11.3px; line-height:1.28; }
        .br-company-name { font-weight:700; color:#ef4444; font-size:12.2px; }

        .title { text-align:center; font-weight:700; font-size:24px; margin:6px 0 2px; }
        .subtitle { text-align:center; font-size:17px; margin:0 0 8px; font-style:italic; }

        .meta { width:100%; border-collapse:collapse; margin-bottom:8px; }
        .meta td { border:1px solid #cfd6df; padding:6px 8px; vertical-align:top; }

        .grid { width:100%; border-collapse:collapse; table-layout:fixed; margin-top:6px; }
        .grid th, .grid td { border:1px solid #334155; padding:5px 6px; font-size:11px; }
        .grid th { background:#eef1f5; text-align:center; font-weight:700; }
        .text-center { text-align:center; }
        .text-right { text-align:right; }
        .money { text-align:right; white-space:nowrap; font-variant-numeric: tabular-nums; }
        .nowrap { white-space:nowrap; }

        .sign { margin-top:14px; width:100%; border-collapse:collapse; }
        .sign td { width:25%; text-align:center; font-size:12px; vertical-align:top; }
        .cap { font-weight:700; }
        .sig { margin-top:28px; font-style:italic; }

        .footer { margin-top:12px; border-top:1px solid #ef4444; padding-top:6px; font-size:10.8px; color:#374151; }
        .footer .name { color:#ef4444; font-weight:700; font-size:12px; }
    </style>
</head>
<body>
@php
    $salesOrder = $delivery->salesOrder;
    $order = $delivery->order;
    $sourceCode = $salesOrder->sales_order_code ?? ($order->order_code ?? ('#' . ($delivery->sales_order_id ?? $delivery->order_id)));

    $receiverName = $salesOrder->invoice_company_name
        ?? $salesOrder->receiver_name
        ?? $order->invoice_company_name
        ?? $order->receiver_name
        ?? '';

    $receiverAddress = $salesOrder->receiver_address
        ?? $order->receiver_address
        ?? '';

    $reason = 'Xuất kho bán hàng theo đơn ' . $sourceCode;
    $deliveryPlace = $receiverAddress;

    $items = $delivery->items ?? collect();
    $totalQty = (int) $items->sum('quantity');
@endphp

<div class="paper">
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
                <div>Email: vigilancevn@gmail.com</div>
            </td>
        </tr>
    </table>

    <div class="title">PHIẾU XUẤT KHO BÁN HÀNG</div>
    <div class="subtitle">Ngày {{ optional($delivery->delivered_at)->format('d') }} tháng {{ optional($delivery->delivered_at)->format('m') }} năm {{ optional($delivery->delivered_at)->format('Y') }}</div>

    <table class="meta">
        <tr>
            <td style="width:70%;">Họ và tên người nhận hàng: <strong>{{ $receiverName }}</strong></td>
            <td style="width:30%;">Mã phiếu: <strong>{{ $delivery->delivery_code }}</strong></td>
        </tr>
        <tr>
            <td colspan="2">Địa chỉ: {{ $receiverAddress }}</td>
        </tr>
        <tr>
            <td colspan="2">Lý do xuất: {{ $reason }}</td>
        </tr>
        <tr>
            <td colspan="2">Địa điểm giao hàng: {{ $deliveryPlace }}</td>
        </tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th style="width:5%;">STT</th>
                <th style="width:12%;">Mã hàng</th>
                <th>Tên hàng</th>
                <th style="width:7%;">SL</th>
                <th style="width:11%;">Đơn giá</th>
                <th style="width:8%;">VAT</th>
                <th style="width:11%;">Tiền thuế</th>
                <th style="width:13%;">Sau thuế</th>
            </tr>
        </thead>
        <tbody>
        @forelse($items as $line)
            @php
                $unitCode = $line->product->serial_number ?? ('SP' . $line->product_id);
                $name = $line->product->name ?? ('Sản phẩm #' . $line->product_id);
                $soItem = $line->salesOrderItem;
                $orderItem = $line->orderItem;
                $qty = (int) ($line->quantity ?? 0);
                $unitPrice = (float) ($soItem->unit_price ?? ($orderItem->unit_price ?? $orderItem->price ?? 0));
                $lineTotal = $unitPrice * $qty;
                $lineVatRate = (float) ($soItem->vat_percent ?? ($salesOrder->vat_percent ?? 0));
                $lineVatAmount = $lineTotal * max(0, $lineVatRate) / 100;
                $lineAfterTax = $lineTotal + $lineVatAmount;
                $vatLabel = $lineVatRate == 0 ? 'KCT/0%' : (rtrim(rtrim(number_format($lineVatRate, 2, '.', ''), '0'), '.') . '%');
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $unitCode }}</td>
                <td>{{ $name }}</td>
                <td class="text-center">{{ $qty }}</td>
                <td class="money">{{ number_format($unitPrice, 0, ',', '.') }}</td>
                <td class="text-center nowrap">{{ $vatLabel }}</td>
                <td class="money">{{ number_format($lineVatAmount, 0, ',', '.') }}</td>
                <td class="money"><strong>{{ number_format($lineAfterTax, 0, ',', '.') }}</strong></td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">Không có dòng xuất kho</td>
            </tr>
        @endforelse
        <tr>
            <td colspan="3" class="text-right"><strong>Cộng</strong></td>
            <td class="text-center"><strong>{{ $totalQty }}</strong></td>
            <td colspan="4"></td>
        </tr>
        </tbody>
    </table>

    <table class="meta" style="margin-top:8px;">
        <tr>
            <td style="width:50%;">Số chứng từ gốc kèm theo: ........................................</td>
            <td style="width:50%; text-align:right;">Ngày ...... tháng ...... năm {{ now()->format('Y') }}</td>
        </tr>
    </table>

    <table class="sign">
        <tr>
            <td><div class="cap">Người lập biểu</div><div class="sig">(Ký, họ tên)</div></td>
            <td><div class="cap">Người nhận hàng</div><div class="sig">(Ký, họ tên)</div></td>
            <td><div class="cap">Thủ kho</div><div class="sig">(Ký, họ tên)</div></td>
            <td><div class="cap">Giám đốc</div><div class="sig">(Ký, họ tên)</div></td>
        </tr>
    </table>

    <div class="footer">
        <div class="name">CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</div>
        <div>Địa chỉ: Phòng B15.09 Tầng 15, Tháp B Tòa nhà Rivergate, 151-155 Bến Vân Đồn, Phường Khánh Hội, TP.HCM</div>
        <div>MST: 0318231312 | Hotline: 02887617015 | Email: vigilancevn@gmail.com | Website: https://vigilance.com.vn</div>
    </div>
</div>
</body>
</html>
