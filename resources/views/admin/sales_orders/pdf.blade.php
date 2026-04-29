<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đơn hàng - {{ $salesOrder->sales_order_code }}</title>
    <style>
        @page { size: A4; margin: 8mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 11px; margin: 0; }

        .quote-a4-wrap { width: 100%; }
        .quote-a4-paper { background: #fff; }
        .quote-a4-pad { padding: 0; }

        .br-header-wrap { background: transparent; border: none; padding: 0; margin: 0 0 10px 0; }
        .br-header { width: 100%; border-collapse: collapse; border: none; }
        .br-header td { vertical-align: middle; border: none; padding: 0; }
        .br-header-left { width: 50%; text-align: left; }
        .br-header-left img { max-width: 185px; height: auto; display: block; }
        .br-header-right { width: 50%; text-align: right; }
        .br-header-right img { max-width: 185px; height: auto; display: inline-block; }
        .br-header-line { height: 1px; background: #dc2626; margin: 8px 0 0 0; padding: 0; border: 0; font-size: 0; line-height: 0; }

        .q-block { border-bottom: 1px solid #111; padding: 8px 0; }
        .q-block-grid { display: table; width: 100%; border-collapse: collapse; }
        .q-block-grid > div { display: table-cell; width: 50%; vertical-align: top; }
        .q-block-grid > div:first-child { border-right: 1px solid #111; padding-right: 12px; }
        .q-col-right { padding-left: 12px; border-left: none; }

        .q-line { margin: 2px 0; line-height: 1.35; }
        .q-label { font-weight: 700; }

        .q-center-title { text-align: center; margin: 9px 0 8px; }
        .q-center-title .t1 { font-size: 20px; letter-spacing: 1px; color: #e53935; font-weight: 700; line-height: 1; }
        .q-center-title .t2 { font-size: 10px; color: #555; margin-top: 2px; }

        .q-redbar {
            background: #d32f2f;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            padding: 5px 8px;
            border: 1px solid #999;
            border-bottom: none;
        }

        .q-table { width: 100%; border-collapse: collapse; font-size: 9.6px; table-layout: fixed; }
        .q-table th, .q-table td {
            border: 1px solid #999;
            padding: 4px 4px;
            line-height: 1.35;
            vertical-align: top;
        }
        .q-table thead th { font-weight: 700; text-align: center; vertical-align: middle; }
        .q-table .sub { font-style: italic; font-weight: 700; font-size: 8.6px; }
        .q-table .left { text-align: left; white-space: normal; word-break: break-word; overflow-wrap: break-word; }
        .q-table .right { text-align: right; white-space: nowrap; }
        .q-table .center { text-align: center; white-space: nowrap; }

        .q-table th:nth-child(1), .q-table td:nth-child(1) { width: 5%; }
        .q-table th:nth-child(2), .q-table td:nth-child(2) { width: 36%; }
        .q-table th:nth-child(3), .q-table td:nth-child(3) { width: 8%; }
        .q-table th:nth-child(4), .q-table td:nth-child(4) { width: 7%; }
        .q-table th:nth-child(5), .q-table td:nth-child(5) { width: 11%; }
        .q-table th:nth-child(6), .q-table td:nth-child(6) { width: 9%; }
        .q-table th:nth-child(7), .q-table td:nth-child(7) { width: 11%; }
        .q-table th:nth-child(8), .q-table td:nth-child(8) { width: 13%; }

        .q-note-label { color: #d32f2f; font-style: italic; font-weight: 700; }
        .muted { color: #555; }

        .q-out-terms { font-size: 10px; line-height: 1.45; margin-top: 10px; }
        .q-out-terms .sep { border-top: 2px solid #999; margin: 8px 0; }
        .q-out-terms ul { margin: 0 0 0 14px; padding: 0; }
        .q-out-terms li { margin: 3px 0; }

        .q-footer { margin-top: 12px; border-top: none; padding-top: 0; font-size: 10px; color: #374151; }
        .q-footer-company { color: #e53935; font-weight: 700; font-size: 12px; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.02em; }
        .q-footer-address { font-size: 10px; line-height: 1.45; margin-bottom: 4px; color: #374151; }
        .q-footer-table { width: 100%; border-collapse: collapse; font-size: 10px; line-height: 1.45; color: #374151; }
        .q-footer-table td { vertical-align: top; padding: 0; }
        .q-footer-table a { color: #1d4ed8; text-decoration: underline; }
        .q-footer-page { text-align: right; white-space: nowrap; font-weight: 700; color: #374151; width: 48px; }

        .q-summary-wrap { margin-top: 6px; display: flex; justify-content: flex-end; }
        .q-summary {
            width: 42%;
            border: 1px solid #9ca3af;
            border-radius: 4px;
            overflow: hidden;
            font-size: 10.5px;
        }
        .q-summary-row { display: table; width: 100%; border-bottom: 1px solid #d1d5db; }
        .q-summary-row:last-child { border-bottom: none; }
        .q-summary-label, .q-summary-value { display: table-cell; padding: 6px 8px; }
        .q-summary-label { width: 62%; text-align: right; background: #f9fafb; font-weight: 700; }
        .q-summary-value { width: 38%; text-align: right; font-weight: 700; }
        .q-summary-row.total .q-summary-label { background: #fee2e2; color: #991b1b; }
        .q-summary-row.total .q-summary-value { background: #fff1f2; color: #991b1b; font-size: 12px; }
    </style>
</head>
<body>
@php
    $items = $salesOrder->items ?? collect();

    $subTotal = (float) $items->sum(function ($item) {
        return (float) ($item->unit_price ?? 0) * (int) ($item->quantity ?? 0);
    });
    $discountPercent = (float) ($salesOrder->discount_percent ?? 0);
    $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
    $vatAmount = (float) $items->sum(function ($item) use ($discountPercent, $salesOrder) {
        $lineSub = (float) ($item->unit_price ?? 0) * (int) ($item->quantity ?? 0);
        $lineAfterDiscount = $lineSub * (1 - ($discountPercent / 100));
        $lineVatRate = (float) ($item->vat_percent ?? $salesOrder->vat_percent ?? 8);
        return $lineAfterDiscount * ($lineVatRate / 100);
    });
    $totalAmount = $afterDiscount + $vatAmount;

    $invoiceName = $salesOrder->invoice_company_name ?: '...';
    $invoiceAddress = $salesOrder->invoice_address ?: '...';
    $receiverPhone = $salesOrder->customer_phone ?: $salesOrder->receiver_phone;
    $receiverEmail = $salesOrder->customer_email ?: '...';
    $receiverTax = $salesOrder->customer_tax_code ?: '...';

    $staffCode = $salesOrder->staff_code ?: '...';
    $salesName = $salesOrder->sales_name ?: '...';

    $companyName = 'CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM';
    $companyTax = '0318231312';
    $companyHotline = '02873026078';

    $pdfLogo1 = public_path('logo1.png');
    $pdfLogo2 = public_path('logo2.png');
    if (! is_file($pdfLogo1)) {
        $pdfLogo1 = public_path('images/vigilance-logo.png');
    }
    if (! is_file($pdfLogo2)) {
        $pdfLogo2 = public_path('images/vigilance-logo.png');
    }
    $pdfLogo1 = str_replace('\\', '/', $pdfLogo1);
    $pdfLogo2 = str_replace('\\', '/', $pdfLogo2);

    $pdfFooterAddress = 'Phòng B15.09 Tầng 15, Tháp B Tòa nhà Rivergate 151-155 Bến Vân Đồn, Phường Khánh Hội, TP.HCM';
    $sellerEmail = 'vigilancevn@gmail.com';
    $sellerWebsite = 'https://www.vigilancevn.com.vn/';
    $sellerWebsiteLabel = 'www.vigilancevn.com.vn';
@endphp

<div class="quote-a4-wrap">
    <div class="quote-a4-paper">
        <div class="quote-a4-pad">
            <div class="br-header-wrap">
                <table class="br-header">
                    <tr>
                        <td class="br-header-left"><img src="{{ $pdfLogo1 }}" alt=""></td>
                        <td class="br-header-right"><img src="{{ $pdfLogo2 }}" alt=""></td>
                    </tr>
                </table>
                <div class="br-header-line"></div>
            </div>

            <div class="q-block">
                <div class="q-block-grid">
                    <div>
                        <div class="q-line"><span class="q-label">CÔNG TY (Address):</span> {{ $invoiceName }}</div>
                        <div class="q-line"><span class="q-label">Địa chỉ (Address):</span> {{ $invoiceAddress }}</div>
                        <div class="q-line"><span class="q-label">Mã số thuế (Tax code):</span> {{ $receiverTax }}</div>
                        <div class="q-line"><span class="q-label">Điện thoại (Tel):</span> {{ $receiverPhone }}</div>
                        <div class="q-line"><span class="q-label">Email:</span> {{ $receiverEmail }}</div>
                        <div class="q-line"><span class="q-label">Người nhận:</span> {{ $salesOrder->receiver_name ?: '---' }}</div>
                        <div class="q-line"><span class="q-label">Địa chỉ giao hàng:</span> {{ $salesOrder->receiver_address ?: '---' }}</div>
                    </div>
                    <div class="q-col-right">
                        <div class="q-line"><span class="q-label">Số đơn hàng (Order No.):</span> {{ $salesOrder->sales_order_code }}</div>
                        <div class="q-line"><span class="q-label">Nguồn báo giá (Quote ref):</span> {{ optional($salesOrder->quote)->quote_code ?: '---' }}</div>
                        <div class="q-line"><span class="q-label">Ngày tạo (Date):</span> {{ optional($salesOrder->created_at)->format('d/m/Y') }}</div>
                        <div class="q-line"><span class="q-label">Mã nhân viên (Staff code):</span> {{ $staffCode }}</div>
                        <div class="q-line"><span class="q-label">Nhân viên phụ trách (Sales):</span> {{ $salesName }}</div>
                    </div>
                </div>
            </div>

            <div class="q-center-title">
                <div class="t1">HÓA ĐƠN HÀNG HÓA</div>
                <div class="t2">(Hệ Thống Tự Động Tạo Hóa Đơn Hàng Hóa)</div>
            </div>

            <div class="q-redbar">SẢN PHẨM CHẤM CÔNG - KIỂM SOÁT CỬA - GIẢI PHÁP KIỂM SOÁT RA VÀO</div>

            <table class="q-table">
                <thead>
                <tr>
                    <th>No.</th>
                    <th>Thông tin chi tiết<br><span class="sub">Details/Description</span></th>
                    <th>Đơn vị<br><span class="sub">Unit</span></th>
                    <th>SL<br><span class="sub">Qty</span></th>
                    <th>Đơn giá<br><span class="sub">Unit Price</span></th>
                    <th>Thuế suất<br><span class="sub">VAT rate</span></th>
                    <th>Tiền thuế<br><span class="sub">VAT amount</span></th>
                    <th>Thành tiền<br><span class="sub">Amount</span></th>
                </tr>
                </thead>
                <tbody>
                @foreach($items as $idx => $item)
                    @php
                        $price = (float) ($item->unit_price ?? 0);
                        $qty = (int) ($item->quantity ?? 0);
                        $lineSub = $price * $qty;
                        $lineAfterDiscount = $lineSub * (1 - ($discountPercent / 100));
                        $lineVatRate = (float) ($item->vat_percent ?? $salesOrder->vat_percent ?? 8);
                        $lineTax = $lineAfterDiscount * ($lineVatRate / 100);
                        $lineAmount = $lineAfterDiscount + $lineTax;
                    @endphp
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="left">{{ $item->product->name ?? ('SP #' . $item->product_id) }}</td>
                        <td class="center">{{ $item->unit ?: '---' }}</td>
                        <td class="center">{{ $qty }}</td>
                        <td class="right">{{ number_format($price, 0, ',', '.') }}</td>
                        <td class="center">{{ $lineVatRate == 0 ? 'KCT/0%' : (rtrim(rtrim(number_format($lineVatRate, 2, '.', ''), '0'), '.') . '%') }}</td>
                        <td class="right">{{ number_format($lineTax, 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($lineAmount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="8" class="left"><span class="q-note-label">Ghi chú:</span> VAT được tính theo từng dòng hàng (thuế suất riêng từng sản phẩm) và được làm tròn theo chứng từ kế toán.</td>
                </tr>
                </tbody>
            </table>

            <div class="q-summary-wrap">
                <div class="q-summary">
                    <div class="q-summary-row">
                        <div class="q-summary-label">Tạm tính</div>
                        <div class="q-summary-value">{{ number_format($subTotal, 0, ',', '.') }}</div>
                    </div>
                    <div class="q-summary-row">
                        <div class="q-summary-label">Chiết khấu ({{ rtrim(rtrim(number_format($discountPercent, 2, '.', ''), '0'), '.') }}%)</div>
                        <div class="q-summary-value">{{ number_format($subTotal - $afterDiscount, 0, ',', '.') }}</div>
                    </div>
                    <div class="q-summary-row">
                        <div class="q-summary-label">VAT (theo từng dòng)</div>
                        <div class="q-summary-value">{{ number_format($vatAmount, 0, ',', '.') }}</div>
                    </div>
                    <div class="q-summary-row total">
                        <div class="q-summary-label">TỔNG CỘNG</div>
                        <div class="q-summary-value">{{ number_format($totalAmount, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="q-out-terms">
                <div class="sep"></div>
                <div><b>Ghi chú:</b></div>
                <div>
                    Hàng hoá được giao miễn phí cho đơn hàng trên 3.000.000 VND, nhưng không quá 50.000 VND/nội thành,
                    80.000 VND/ngoại thành; với các mặt hàng cồng kềnh, có kích thước lớn sẽ được giao hàng tại kho của
                    Vigilance Việt Nam hoặc khách hàng tự thanh toán chi phí vận chuyển.
                </div>
                <div style="margin-top:4px;">
                    Giao hàng ngay sau khi thực hiện xác nhận đơn hàng và thực hiện đúng thủ tục thanh toán, đến:
                </div>

                <div class="sep"></div>
                <div>
                    <b>{{ $invoiceName }}</b>
                    @if(!empty($salesOrder->receiver_address))
                        Địa chỉ: {{ $salesOrder->receiver_address }}
                    @endif
                    @if(!empty($salesOrder->receiver_name))
                        Người nhận hàng: {{ $salesOrder->receiver_name }}
                    @endif
                    @if(!empty($salesOrder->receiver_phone))
                        Điện thoại: {{ $salesOrder->receiver_phone }}
                    @endif
                </div>

                <div class="sep"></div>
                <div>
                    <b>Phương thức thanh toán:</b>
                    @if(($salesOrder->payment_term ?? 'full_advance') === 'debt')
                        Thời gian thanh toán {{ (int) ($salesOrder->payment_due_days ?? 0) }} ngày kể từ ngày giao hàng/hóa đơn.
                    @elseif(($salesOrder->payment_term ?? 'full_advance') === 'deposit')
                        Thanh toán đặt cọc {{ (float) ($salesOrder->deposit_percent ?? 0) }}%, phần còn lại thanh toán theo thoả thuận.
                    @else
                        Thanh toán 100% bằng tiền mặt khi giao hàng hoặc Chuyển khoản 100% trước khi giao hàng.
                    @endif
                </div>
                <div style="margin-top:2px;">
                    Chi tiết như sau: Phần tạm ứng trước khi lấy hàng: {{ number_format($totalAmount, 0, ',', '.') }} VND
                    (tương đương 100%) cho Tổng giá trị đơn hàng: {{ number_format($totalAmount, 0, ',', '.') }} VND,
                    số tiền thanh toán còn lại (nếu có): 0,00 VND.
                </div>
                @if(!empty($salesOrder->payment_note))
                    <div style="margin-top:2px;"><i>Ghi chú thanh toán:</i> {{ $salesOrder->payment_note }}</div>
                @endif

                <div class="sep"></div>
                <div><b>Đơn vị thụ hưởng: CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</b></div>
                <div style="margin-top:2px;">Số tài khoản ACB (ACB Account): 261223888 - Ngân hàng TMCP Á Châu - Chi nhánh Sài Gòn</div>
                <div style="color:#d32f2f; font-weight:700; font-style:italic; margin-top:2px;">
                    (Xin lưu ý, chúng tôi chỉ sử dụng một tài khoản doanh nghiệp, mọi tài khoản khác đều không được chấp nhận thanh toán)
                </div>

                <div class="sep"></div>
                <div>
                    <b>Bảo hành (Warranty):</b> Tất cả sản phẩm do Vigilance Việt Nam phân phối đều được tiếp nhận và bảo hành theo tiêu chuẩn
                    của nhà sản xuất tại trụ sở công ty. Sản phẩm được đổi mới 100% nếu có phát sinh lỗi do sản xuất trong thời hạn quy định
                    và kèm đầy đủ chứng từ.
                </div>

                <div class="sep"></div>
                <div>
                    <b>Ghi chú thêm:</b> Các đơn hàng đặc biệt chỉ được huỷ ngang trong quá trình sản xuất khi có xác nhận từ nhà sản xuất.
                    Không chấp nhận trả hàng trong các điều kiện ngoài phạm vi quy định.
                </div>
            </div>

            <div class="q-footer">
                <div class="q-footer-company">{{ $companyName }}</div>
                <div class="q-footer-address">Địa chỉ: {{ $pdfFooterAddress }}</div>
                <table class="q-footer-table">
                    <tr>
                        <td>
                            Mã số thuế: {{ $companyTax }}
                            | Hotline: {{ $companyHotline }}
                            | Email : <a href="mailto:{{ $sellerEmail }}">{{ $sellerEmail }}</a>
                            | Website: <a href="{{ $sellerWebsite }}">{{ $sellerWebsiteLabel }}</a>
                        </td>
                        <td class="q-footer-page">1 / 1</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
