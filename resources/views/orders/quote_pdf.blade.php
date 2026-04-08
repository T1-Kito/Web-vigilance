<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo giá - {{ $order->quote_code ?? $order->order_code }}</title>
    <style>
        @page { size: A4; margin: 8mm; }
        body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 11px; margin: 0; }

        .quote-a4-wrap { width: 100%; }
        .quote-a4-paper { background: #fff; }
        .quote-a4-pad { padding: 0; }

        /* Header: không khung, không nền — chỉ 2 logo + 1 đường kẻ đỏ ngang */
        .br-header-wrap {
            background: transparent;
            border: none;
            padding: 0;
            margin: 0 0 10px 0;
        }
        .br-header { width: 100%; border-collapse: collapse; border: none; }
        .br-header td { vertical-align: middle; border: none; padding: 0; }
        .br-header-left { width: 50%; text-align: left; }
        .br-header-left img { max-width: 185px; height: auto; display: block; }
        .br-header-right { width: 50%; text-align: right; }
        .br-header-right img { max-width: 185px; height: auto; display: inline-block; }
        .br-header-line {
            height: 1px;
            background: #dc2626;
            margin: 8px 0 0 0;
            padding: 0;
            border: 0;
            font-size: 0;
            line-height: 0;
        }

        /* Khung nội dung: chỉ viền trên + dưới; không viền trái/phải; gạch dọc giữa 2 cột */
        .q-block {
            border: none;

            border-bottom: 1px solid #111;
            padding: 8px 0;
        }
        .q-block-grid { display: table; width: 100%; border-collapse: collapse; }
        .q-block-grid > div { display: table-cell; width: 50%; vertical-align: top; }
        .q-block-grid > div:first-child {
            border-right: 1px solid #111;
            padding-right: 12px;
        }
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
        .q-table th:nth-child(2), .q-table td:nth-child(2) { width: 34%; }
        .q-table th:nth-child(3), .q-table td:nth-child(3) { width: 10%; }
        .q-table th:nth-child(4), .q-table td:nth-child(4) { width: 9%; }
        .q-table th:nth-child(5), .q-table td:nth-child(5) { width: 7%; }
        .q-table th:nth-child(6), .q-table td:nth-child(6) { width: 11%; }
        .q-table th:nth-child(7), .q-table td:nth-child(7) { width: 12%; }
        .q-table th:nth-child(8), .q-table td:nth-child(8) { width: 12%; }

        .q-table td:nth-child(6),
        .q-table td:nth-child(7),
        .q-table td:nth-child(8) {
            font-size: 9.2px;
            letter-spacing: -0.1px;
        }
        .q-table .bold { font-weight: 700; }
        .q-table .q-note { font-size: 10px; }
        .q-note-label { color: #d32f2f; font-style: italic; font-weight: 700; }
        .muted { color: #555; }
        .q-prod-img { width: 30px; height: 30px; object-fit: contain; }

        .q-out-terms { font-size: 10px; line-height: 1.45; margin-top: 10px; }
        .q-out-terms .sep { border-top: 2px solid #999; margin: 8px 0; }
        .q-out-terms ul { margin: 0 0 0 14px; padding: 0; }
        .q-out-terms li { margin: 3px 0; }

        /* Footer mẫu gốc: viền trên đỏ nhạt, tên công ty đỏ, địa chỉ + dòng liên hệ, link xanh */
        .q-footer {
            margin-top: 12px;
            border-top: none;
            padding-top: 0;
            font-size: 10px;
            color: #374151;
        }
        .q-footer-company {
            color: #e53935;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.02em;
        }
        .q-footer-address {
            font-size: 10px;
            line-height: 1.45;
            margin-bottom: 4px;
            color: #374151;
        }
        .q-footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            line-height: 1.45;
            color: #374151;
        }
        .q-footer-table td { vertical-align: top; padding: 0; }
        .q-footer-table a {
            color: #1d4ed8;
            text-decoration: underline;
        }
        .q-footer-page {
            text-align: right;
            white-space: nowrap;
            font-weight: 700;
            color: #374151;
            width: 48px;
        }
    </style>
</head>
<body>
@php
    $discountPercent = (float) ($order->discount_percent ?? 0);
    $vatPercent = (float) ($order->vat_percent ?? 8);

    $items = $order->items ?? collect();

    $orderCode = $order->order_code ?? ('VK' . str_pad($order->id, 6, '0', STR_PAD_LEFT));

    $companyName = $order->company_name ?: 'CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM';
    $companyTax = $order->company_tax_code ?: '0318231312';
    $companyHotline = $order->company_hotline ?: '02873026078';
    $companyAddress = $order->company_address ?: '151-155 Bến Vân Đồn, Phường Khánh Hội, TP HCM';

    $invoiceName = $order->invoice_company_name ?: '...';
    $invoiceAddress = $order->invoice_address ?: '...';
    $receiverPhone = $order->customer_phone ?: $order->receiver_phone;
    $receiverEmail = $order->customer_email ?: '...';
    $receiverTax = $order->customer_tax_code ?: '...';

    $staffCode = $order->staff_code ?: ($order->user->name ?? '...');
    $salesName = $order->sales_name ?: ($order->user->name ?? '...');

    $totalBeforeTax = 0;
    $totalTax = 0;
    $totalAmount = 0;

    if (!function_exists('vn_read_number_pdf')) {
        function vn_read_number_pdf($number)
        {
            $number = (int) $number;
            if ($number === 0) return 'không';
            $units = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];

            $readThree = function ($n) use ($units) {
                $n = (int) $n;
                $hundreds = intdiv($n, 100);
                $tensUnits = $n % 100;
                $tens = intdiv($tensUnits, 10);
                $unit = $tensUnits % 10;
                $s = '';

                if ($hundreds > 0) $s .= $units[$hundreds] . ' trăm';
                if ($tens > 1) {
                    $s .= ($s ? ' ' : '') . $units[$tens] . ' mươi';
                    if ($unit === 1) $s .= ' mốt';
                    elseif ($unit === 5) $s .= ' lăm';
                    elseif ($unit > 0) $s .= ' ' . $units[$unit];
                } elseif ($tens === 1) {
                    $s .= ($s ? ' ' : '') . 'mười';
                    if ($unit === 5) $s .= ' lăm';
                    elseif ($unit > 0) $s .= ' ' . $units[$unit];
                } else {
                    if ($unit > 0) {
                        if ($hundreds > 0) $s .= ' lẻ';
                        if ($unit === 5 && $hundreds > 0) $s .= ' lăm';
                        else $s .= ($s ? ' ' : '') . $units[$unit];
                    }
                }
                return trim($s);
            };

            $groups = ['', ' nghìn', ' triệu', ' tỷ'];
            $parts = [];
            $i = 0;
            while ($number > 0) {
                $chunk = $number % 1000;
                if ($chunk > 0) $parts[] = $readThree($chunk) . $groups[$i];
                $number = intdiv($number, 1000);
                $i++;
            }
            return implode(' ', array_reverse($parts));
        }
    }

    // Logo: file thật nằm ở public/logo1.png, public/logo2.png (không phải public/images/).
    // DomPDF trên Windows thường ổn định hơn với đường dẫn dùng dấu /.
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

    $pdfFooterAddress = trim((string) ($order->company_address ?? ''));
    if ($pdfFooterAddress === '') {
        $pdfFooterAddress = 'Phòng B15.09 Tầng 15, Tháp B Tòa nhà Rivergate 151-155 Bến Vân Đồn, Phường Khánh Hội, TP.HCM';
    }
    $sellerEmail = trim((string) ($order->company_email ?? ''));
    if ($sellerEmail === '') {
        $sellerEmail = 'vigilancevn@gmail.com';
    }
    $sellerWebsite = 'https://www.vigilancevn.com.vn/';
    $sellerWebsiteLabel = 'www.vigilancevn.com.vn';
@endphp

<div class="quote-a4-wrap">
    <div class="quote-a4-paper">
        <div class="quote-a4-pad">
            <div class="br-header-wrap">
                <table class="br-header">
                    <tr>
                        <td class="br-header-left">
                            <img src="{{ $pdfLogo1 }}" alt="">
                        </td>
                        <td class="br-header-right">
                            <img src="{{ $pdfLogo2 }}" alt="">
                        </td>
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
                        <div class="q-line"><span class="q-label">Người nhận:</span> {{ $order->receiver_name }}</div>
                        <div class="q-line"><span class="q-label">Địa chỉ giao hàng:</span> {{ $order->receiver_address }}</div>
                    </div>
                    <div class="q-col-right">
                        <div class="q-line"><span class="q-label">Số báo giá (Quote No.):</span> {{ $orderCode }}</div>
                        <div class="q-line"><span class="q-label">Ngày báo giá (Date):</span> {{ optional($order->created_at)->format('d/m/Y') }}</div>
                        <div class="q-line"><span class="q-label">Mã nhân viên (Staff code):</span> {{ $staffCode }}</div>
                        <div class="q-line"><span class="q-label">Báo giá được làm bởi (Sales):</span> {{ $salesName }}</div>
                    </div>
                </div>
            </div>

            <div class="q-center-title">
                <div class="t1">BÁO GIÁ</div>
                <div class="t2">(Hệ Thống Tự Động Tạo Báo Giá)</div>
            </div>

            <div class="q-redbar">SẢN PHẨM CHẤM CÔNG - KIỂM SOÁT CỬA - GIẢI PHÁP KIỂM SOÁT RA VÀO</div>

            <table class="q-table">
                <thead>
                <tr>
                    <th style="width:5%;">No.</th>
                    <th style="width:34%;">Thông tin chi tiết<br><span class="sub">Details/Description</span></th>
                    <th style="width:10%;">Hình ảnh<br><span class="sub">Product Image</span></th>
                    <th style="width:9%;">Bảo hành<br><span class="sub">Warranty</span></th>
                    <th style="width:7%;">SL<br><span class="sub">Quantity</span></th>
                    <th style="width:11%;">Đơn giá<br><span class="sub">Units Price</span></th>
                    <th style="width:12%;">Thuế (VAT)<br><span class="sub">VAT {{ (int) $vatPercent }}%</span></th>
                    <th style="width:12%;">Trọng tiền<br><span class="sub">Amount</span></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="8" class="left"><b>Phụ kiện thích hợp</b> <span class="sub">/ Optional Accessories</span></td>
                </tr>

                @foreach($items as $idx => $item)
                    @php
                        $name = $item->product->name ?? 'Sản phẩm';
                        $productImage = $item->product->image ?? null;
                        $price = (float) ($item->price ?? 0);
                        $qty = (int) ($item->quantity ?? 0);
                        $lineSub = $price * $qty;
                        $lineTax = $lineSub * ($vatPercent / 100);
                        $lineAmount = $lineSub + $lineTax;

                        $totalBeforeTax += $lineSub;
                        $totalTax += $lineTax;
                        $totalAmount += $lineAmount;
                    @endphp
                    <tr>
                        <td class="center">{{ $idx + 1 }}</td>
                        <td class="left">{{ $name }}</td>
                        <td class="center">
                            @if(!empty($productImage))
                                <img class="q-prod-img" src="{{ public_path('images/products/' . $productImage) }}" alt="{{ $name }}">
                            @endif
                        </td>
                        <td class="center">12 Tháng</td>
                        <td class="center">{{ $qty }}</td>
                        <td class="right">{{ number_format($price, 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($lineTax, 0, ',', '.') }}</td>
                        <td class="right">{{ number_format($lineAmount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                <tr>
                    <td colspan="8" class="q-note"><span class="q-note-label">Ghi chú</span> &nbsp;giao hàng tại kho chưa bao gồm phí vận chuyển</td>
                </tr>

                @php
                    $amountInWords = ucfirst(vn_read_number_pdf((int) round($totalAmount))) . ' đồng';
                @endphp

                <tr>
                    <td class="right bold" colspan="7">Tổng cộng giá trị đơn hàng</td>
                    <td class="right bold">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="right" colspan="8"><span class="bold">Bằng chữ (by word):</span> <span class="muted">{{ $amountInWords }}</span></td>
                </tr>
                <tr>
                    <td colspan="8" class="left bold">Thời hạn &amp; các điều khoản khác <span class="muted">/ Term &amp; Conditions:</span></td>
                </tr>
                <tr>
                    <td colspan="8" class="left">
                        <div>Lưu ý / Note: Giá trên không bao gồm các chi phí vật tư phụ &amp; công lắp đặt; chưa tính phụ phí làm thêm thứ 7, chủ nhật và các ngày lễ.</div>
                        <div class="muted">(The above price does not include the cost of additional materials &amp; installation labor on Saturdays, Sundays and public holidays.)</div>
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="q-out-terms">
                <div class="sep"></div>

                <ul>
                    <li>- Thời gian giao hàng (Delivery): Giao hàng ngay sau khi thực hiện xác nhận đơn hàng và thực hiện đúng thủ tục thanh toán.</li>
                    <li><b>- Hàng hoá (thiết bị) được giao trong khu vực TP HCM có mức phí như sau:</b></li>
                    <li>- Phí giao hàng áp dụng cho khu vực Tp HCM nội thành và ngoại thành từ 3.000.000 VND/đơn hàng sẽ được miễn phí giao hàng trong bán kính 5Km, trường hợp còn lại nội thành là 50.000 VND/đơn hàng, ngoại thành là 80.000 VND/đơn hàng.</li>
                    <li>- Đối với trường hợp ngoại tỉnh, khác khu vực Tp HCM khách hàng tự chọn đơn vị vận chuyển, và Vigilance sẽ giao hàng tại kho hàng Tp HCM (Chi phí này người mua sẽ tự thoả thuận với đơn vị vận chuyển hoặc liên hệ nhân viên kinh doanh để được hỗ trợ.)</li>
                </ul>

                <div class="sep"></div>

                <ul>
                    <li>- Thời gian thanh toán (Payment term): Thanh toán 100% bằng tiền mặt khi giao hàng hoặc Chuyển khoản 100% trước khi giao hàng/100% payment in cash on delivery or 100% bank transfer before delivery.</li>
                    <li>Chi tiết như sau: Phần tạm ứng trước khi lấy hàng: {{ number_format($totalAmount, 0, ',', '.') }} VND (tương đương 100 %) cho Tổng giá trị đơn hàng: {{ number_format($totalAmount, 0, ',', '.') }} VND số tiền thanh toán còn lại (nếu có): 0,00 VND.</li>
                    <li>- Giá trị báo giá có hiệu lực đến ngày (Validity quotations to days): {{ optional($order->created_at)->addDays(15)->format('d/m/Y') }}</li>
                </ul>

                <div class="sep"></div>

                <div><b>Bảo hành (Warranty):</b> Tất cả sản phẩm do Vigilance Việt Nam phân phối đều được tiếp nhận và bảo hành tại trụ sở chính của công ty: 96 Đường số 14, KDC Him Lam, Phường Tân Hưng,TP.HCM.</div>

                <div class="sep"></div>

                <div>- Tài khoản ngân hàng/ Account Bank:</div>
                <div>&nbsp;&nbsp;&nbsp;&nbsp;Tên đơn vị thụ hưởng: {{ $companyName }}</div>
                <div>&nbsp;&nbsp;&nbsp;&nbsp;Số tài khoản ACB (ACB Account): 261223888 - Ngân hàng TMCP Á Châu - Chi nhánh Sài Gòn</div>
                <div class="muted"><b>(Xin lưu ý chúng tôi chỉ sử dụng 01 tài khoản tên với tên doanh nghiệp, mọi tài khoản doanh nghiệp khác đều không được chấp nhận thanh toán)</b></div>

                <div class="sep"></div>

                <div><b>Những sản phẩm không liệt kê trong danh mục trong báo giá này đều xem là chi phí phát sinh./Products not listed in the list on this quote are considered incurred costs.</b></div>
            </div>

            <div class="q-footer">
                <div class="q-footer-company">CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</div>
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
