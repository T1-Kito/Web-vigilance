<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In phiếu xuất kho {{ $delivery->delivery_code }}</title>
    <style>
        @page { size: A4; margin: 10mm 12mm 12mm 12mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f4f6;
            color: #111827;
            font-size: 12px;
        }

        .toolbar {
            max-width: 900px;
            margin: 12px auto 0;
            display: flex;
            gap: 8px;
        }
        .toolbar button {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #111827;
            color: #fff;
            cursor: pointer;
        }
        .toolbar button:last-child {
            background: #fff;
            color: #111827;
        }

        .paper {
            width: 210mm;
            min-height: 297mm;
            margin: 10px auto 18px;
            background: #fff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 24px rgba(0,0,0,.08);
            padding: 12mm 12mm 18mm;
            position: relative;
        }

        .header-logos {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .br-header-left img {
            max-width: 160px;
            height: auto;
            object-fit: contain;
        }
        .br-header-right img {
            max-width: 190px;
            height: auto;
            margin-bottom: 20px;
            object-fit: contain;
        }
        .line-red {
            border-top: 1px solid #ef4444;
            margin-bottom: 12px;
        }

        .doc-title {
            text-align: center;
            margin-bottom: 10px;
        }
        .doc-title h1 {
            margin: 0;
            font-size: 26px;
            letter-spacing: .2px;
            font-weight: 700;
        }
        .doc-title .code {
            margin-top: 2px;
            font-size: 13px;
            color: #374151;
        }

        .meta-right {
            text-align: right;
            font-size: 12px;
            color: #374151;
            margin-bottom: 8px;
        }

        .info-grid {
            font-size: 12px;
            margin-bottom: 10px;
        }
        .info-row {
            padding: 6px 0;
        }
        .info-row .label {
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th, td {
            border: 1px solid #9ca3af;
            padding: 6px 7px;
            font-size: 12px;
        }
        th {
            background: #f3f4f6;
            text-align: left;
            font-weight: 700;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .nowrap { white-space: nowrap; }
        .money { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 26px;
            font-size: 13px;
        }
        .sig-col {
            width: 31%;
            text-align: center;
        }
        .sig-space { height: 66px; }

        .footer {
            position: absolute;
            left: 12mm;
            right: 12mm;
            bottom: 8mm;
            border-top: 1px solid #ef4444;
            padding-top: 6px;
            font-size: 11px;
            color: #6b7280;
            background: #fff;
        }
        .footer .name {
            color: #ef4444;
            font-weight: 700;
            margin-bottom: 2px;
        }

        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .paper {
                margin: 0;
                width: auto;
                min-height: auto;
                border: none;
                box-shadow: none;
                padding: 0;
            }
            .footer {
                position: fixed;
                left: 12mm;
                right: 12mm;
                bottom: 8mm;
            }
        }
    </style>
</head>
<body>
@php
    $order = $delivery->order;
    $salesOrder = $delivery->salesOrder;
    $sourceCode = $salesOrder->sales_order_code ?? ($order->order_code ?? ('#' . ($delivery->sales_order_id ?? $delivery->order_id ?? $delivery->id)));
@endphp

<div class="toolbar">
    <button onclick="window.print()">In ngay</button>
    <button onclick="window.close()">Đóng</button>
</div>

<div class="paper">
    <div class="header-logos">
        <div class="br-header-left">
            <img src="{{ asset('logo1.png') }}" alt="Vigilance" onerror="this.style.visibility='hidden'">
        </div>
        <div class="br-header-right">
            <img src="{{ asset('logo2.png') }}" alt="VKS" onerror="this.style.visibility='hidden'">
        </div>
    </div>
    <div class="line-red"></div>

    <div class="doc-title">
        <h1>PHIẾU XUẤT KHO</h1>
        <div class="code">(Số: {{ $delivery->delivery_code }})</div>
    </div>

    <div class="meta-right">TP.HCM, ngày {{ optional($delivery->delivered_at)->format('d') ?: now()->format('d') }} tháng {{ optional($delivery->delivered_at)->format('m') ?: now()->format('m') }} năm {{ optional($delivery->delivered_at)->format('Y') ?: now()->format('Y') }}</div>

    <div class="info-grid">
        <div class="info-row"><span class="label">Nguồn đơn:</span> {{ $sourceCode }}</div>
        <div class="info-row"><span class="label">Họ và tên người nhận hàng:</span> {{ $delivery->receiver_name ?: '---' }}</div>
        <div class="info-row"><span class="label">Địa chỉ:</span> {{ $delivery->receiver_address ?: '---' }}</div>
        <div class="info-row"><span class="label">Lý do xuất:</span> {{ $delivery->delivery_reason ?: '---' }}</div>
        <div class="info-row"><span class="label">Địa điểm giao hàng:</span> {{ $delivery->delivery_location ?: '---' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:42px;" class="text-center">STT</th>
                <th>Tên hàng</th>
                <th style="width:62px;" class="text-center">ĐVT</th>
                <th style="width:60px;" class="text-center">SL</th>
                <th style="width:86px;" class="money">Đơn giá</th>
                <th style="width:68px;" class="text-center">VAT</th>
                <th style="width:88px;" class="money">Tiền thuế</th>
                <th style="width:96px;" class="money">Sau thuế</th>
            </tr>
        </thead>
        <tbody>
        @forelse($delivery->items as $idx => $line)
            @php
                $soItem = $line->salesOrderItem;
                $orderItem = $line->orderItem;
                $unit = $soItem->unit ?? ($orderItem->unit ?? '---');
                $unitPrice = (float) ($soItem->unit_price ?? ($orderItem->unit_price ?? $orderItem->price ?? 0));
                $qty = (int) ($line->quantity ?? 0);
                $lineTotal = $unitPrice * $qty;
                $lineVatRate = (float) ($soItem->vat_percent ?? ($salesOrder->vat_percent ?? 0));
                $lineVatAmount = $lineTotal * max(0, $lineVatRate) / 100;
                $lineAfterTax = $lineTotal + $lineVatAmount;
                $vatLabel = $lineVatRate == 0 ? 'KCT/0%' : (rtrim(rtrim(number_format($lineVatRate, 2, '.', ''), '0'), '.') . '%');
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>{{ $line->product->name ?? ('Sản phẩm #' . $line->product_id) }}</td>
                <td class="text-center">{{ $unit }}</td>
                <td class="text-center">{{ $qty }}</td>
                <td class="money">{{ number_format($unitPrice, 0, ',', '.') }}</td>
                <td class="text-center nowrap">{{ $vatLabel }}</td>
                <td class="money">{{ number_format($lineVatAmount, 0, ',', '.') }}</td>
                <td class="money"><strong>{{ number_format($lineAfterTax, 0, ',', '.') }}</strong></td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center" style="color:#6b7280;">Không có dòng hàng.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="signatures">
        <div class="sig-col">
            <div><strong>Người lập phiếu</strong></div>
            <div class="sig-space"></div>
            <div>(Ký, ghi rõ họ tên)</div>
        </div>
        <div class="sig-col">
            <div><strong>Người giao hàng</strong></div>
            <div class="sig-space"></div>
            <div>(Ký, ghi rõ họ tên)</div>
        </div>
        <div class="sig-col">
            <div><strong>Người nhận hàng</strong></div>
            <div class="sig-space"></div>
            <div>(Ký, ghi rõ họ tên)</div>
        </div>
    </div>

    <div class="footer">
        <div class="name">CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</div>
        <div>Địa chỉ: Phòng B15.09 Tầng 15, Tháp B Tòa nhà Rivergate 151-155 Bến Vân Đồn, Phường Khánh Hội, TP.HCM</div>
        <div>Mã số thuế: 0318231312 | Hotline: 0982751075 | Website: www.vigilancevn.com.vn</div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 160);
    });
</script>
</body>
</html>
