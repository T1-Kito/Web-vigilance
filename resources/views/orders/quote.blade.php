@extends('layouts.user')

@section('title', 'Báo giá')

@section('content')
@php
    $discountPercent = (float) ($order->discount_percent ?? 0);
    $vatPercent = (float) ($order->vat_percent ?? 8);

    $selectedPaymentTerm = old('payment_term', $order->payment_term ?? 'full_advance');
    $selectedPaymentDueDays = old('payment_due_days', $order->payment_due_days ?? null);
    $selectedDepositPercent = old('deposit_percent', $order->deposit_percent ?? null);
    $selectedPaymentNote = old('payment_note', $order->payment_note ?? $order->note ?? '');
    $selectedPaymentMethod = old('payment_method', $order->payment_method ?? 'bank_transfer');

    $items = $order->items ?? collect();

    $orderCode = $order->quote_code ?? $order->order_code ?? ("VK" . str_pad($order->id, 6, '0', STR_PAD_LEFT));

    $companyName = $order->company_name ?: 'CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM';
    $companyTax = $order->company_tax_code ?: '0318231312';
    $companyHotline = $order->company_hotline ?: '02873026078';

    $invoiceName = $order->invoice_company_name ?: '...';
    $invoiceAddress = $order->invoice_address ?: '...';
    $receiverPhone = $order->customer_phone ?: $order->receiver_phone;
    $receiverEmail = $order->customer_email ?: '...';
    $receiverTax = $order->customer_tax_code ?: '...';
    $receiverAtt = $order->customer_contact_person ?: '...';

    $staffCode = $order->staff_code ?: ($order->user->name ?? '...');
    $salesName = $order->sales_name ?: ($order->user->name ?? '...');

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

    $webLogo1 = is_file(public_path('logo1.png')) ? asset('logo1.png') : asset('images/vigilance-logo.png');
    $webLogo2 = is_file(public_path('logo2.png')) ? asset('logo2.png') : asset('images/vigilance-logo.png');

    $totalBeforeTax = 0;
    $totalTax = 0;
    $totalAmount = 0;

    if (!function_exists('vn_read_number')) {
        function vn_read_number($number)
        {
            $number = (int) $number;
            if ($number === 0) {
                return 'không';
            }

            $units = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];

            $readThree = function ($n) use ($units) {
                $n = (int) $n;
                $hundreds = intdiv($n, 100);
                $tensUnits = $n % 100;
                $tens = intdiv($tensUnits, 10);
                $unit = $tensUnits % 10;

                $s = '';
                if ($hundreds > 0) {
                    $s .= $units[$hundreds] . ' trăm';
                }

                if ($tens > 1) {
                    $s .= ($s ? ' ' : '') . $units[$tens] . ' mươi';
                    if ($unit === 1) {
                        $s .= ' mốt';
                    } elseif ($unit === 5) {
                        $s .= ' lăm';
                    } elseif ($unit > 0) {
                        $s .= ' ' . $units[$unit];
                    }
                } elseif ($tens === 1) {
                    $s .= ($s ? ' ' : '') . 'mười';
                    if ($unit === 5) {
                        $s .= ' lăm';
                    } elseif ($unit > 0) {
                        $s .= ' ' . $units[$unit];
                    }
                } else {
                    if ($unit > 0) {
                        if ($hundreds > 0) {
                            $s .= ' lẻ';
                        }
                        if ($unit === 5 && $hundreds > 0) {
                            $s .= ' lăm';
                        } else {
                            $s .= ($s ? ' ' : '') . $units[$unit];
                        }
                    }
                }

                return trim($s);
            };

            $groups = [
                0 => '',
                1 => ' nghìn',
                2 => ' triệu',
                3 => ' tỷ',
            ];

            $parts = [];
            $groupIndex = 0;
            while ($number > 0) {
                $chunk = $number % 1000;
                if ($chunk > 0) {
                    $text = $readThree($chunk);
                    $parts[] = $text . $groups[$groupIndex];
                }
                $number = intdiv($number, 1000);
                $groupIndex++;
            }

            $parts = array_reverse($parts);
            return trim(implode(' ', $parts));
        }
    }
@endphp

@php
    $isPrintMode = (string) request()->query('print') === '1';
@endphp

<style>
    .quote-a4-wrap { max-width: 980px; margin: 0 auto; }
    .quote-a4-paper { background: #fff; }
    .quote-a4-pad { padding: 14px 16px; }

    /* Header giống PDF: 2 logo + gạch đỏ, không khung chữ công ty */
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

    /* Khối khách: chỉ viền dưới + gạch dọc giữa (giống PDF) */
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
    .q-line { margin: 2px 0; line-height: 1.35; font-size: 12px; }
    .q-label { font-weight: 700; }

    .q-center-title { text-align: center; margin: 9px 0 8px; }
    .q-center-title .t1 { font-size: 20px; letter-spacing: 1px; color: #e53935; font-weight: 700; line-height: 1; }
    .q-center-title .t2 { font-size: 10px; color: #555; margin-top: 2px; font-style: normal; font-weight: 400; }

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

    .q-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    .q-table th, .q-table td { border: 1px solid #999; padding: 4px 5px; line-height: 1.35; vertical-align: top; }
    .q-table thead th { font-weight: 700; text-align: center; vertical-align: middle; }
    .muted { color: #555; }
    .q-table .sub { font-style: italic; font-weight: 700; font-size: 10px; }
    .q-table .left { text-align: left; }
    .q-table .right { text-align: right; }
    .q-table .center { text-align: center; }
    .q-prod-img { width: 54px; height: 54px; object-fit: contain; display: block; margin: 0 auto; }

    .q-note { font-weight: 500; text-align: center; font-size: 11px; padding: 6px 0; }
    .q-note .q-note-label { color: #d32f2f; }

    .q-summary { width: 100%; border-collapse: collapse; font-size: 11px; }
    .q-summary td { border: 1px solid #999; padding: 6px 8px; }
    .q-summary .right { text-align: right; }
    .q-summary .bold { font-weight: 900; }

    .q-terms { font-size: 10.5px; line-height: 1.45; }
    .q-terms .head { font-weight: 900; margin-bottom: 4px; }
    .q-terms .muted { font-style: italic; }
    .q-terms .hr { height: 1px; background: #999; margin: 8px 0; }
    .q-terms ul { margin: 6px 0 0 18px; padding: 0; }
    .q-terms li { margin: 4px 0; }

    .q-out-terms { font-size: 10px; line-height: 1.45; margin-top: 10px; }
    .q-out-terms .sep { border-top: 2px solid #999; margin: 8px 0; }
    .q-out-terms ul { margin: 0 0 0 14px; padding: 0; }
    .q-out-terms li { margin: 3px 0; }

    /* Footer giống PDF */
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

    .no-print { display: block; }

    .misa-form { border: 1px solid #dbe3ef; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 6px 18px rgba(15, 23, 42, .06); }
    .misa-form__head { padding: 12px 14px; background: linear-gradient(180deg, #f8fbff, #ffffff); border-bottom: 1px solid #e6edf7; }
    .misa-form__title { font-weight: 800; font-size: 14px; color: #0f172a; }
    .misa-form__sub { font-size: 12px; color: #64748b; margin-top: 2px; }
    .misa-form__body { padding: 12px; }
    .misa-label { font-size: 12px; color: #334155; font-weight: 700; margin-bottom: 6px; }
    .misa-control { font-size: 13px; padding: 9px 10px; border-radius: 10px; border: 1px solid #dbe3ef; background: #fff; }
    .misa-control:focus { border-color: #2563eb; box-shadow: 0 0 0 .18rem rgba(37,99,235,.14); }
    .misa-choice-group { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .misa-choice { border: 1px solid #dbe3ef; border-radius: 10px; padding: 9px 10px; display: flex; align-items: center; gap: 8px; background: #fff; }
    .misa-choice input { margin: 0; }
    .misa-choice span { font-size: 12.5px; font-weight: 600; color: #1e293b; }
    .misa-inline-note { font-size: 11px; color: #64748b; margin-top: 4px; }
    .misa-actions { display: flex; justify-content: flex-end; }
    .misa-submit { border-radius: 10px; font-weight: 800; padding: 10px 16px; min-width: 180px; }
    @media (max-width: 768px) {
        .misa-choice-group { grid-template-columns: 1fr; }
        .misa-actions { justify-content: stretch; }
        .misa-submit { width: 100%; }
    }

    @page { size: A4; margin: 8mm; }
    @media print {
        .no-print { display: none !important; }

        /* Chỉ in đúng vùng báo giá */
        body * {
            visibility: hidden !important;
        }

        .quote-a4-wrap,
        .quote-a4-wrap * {
            visibility: visible !important;
        }

        .quote-a4-wrap {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            z-index: 9999 !important;
            background: #fff !important;
        }

        .container,
        .quote-a4-paper {
            border: none !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        .quote-a4-pad { padding: 0 !important; }
    }
</style>

<div class="container py-4">
    <div class="quote-a4-wrap">
        <div class="no-print mb-3">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(auth()->check() && (auth()->user()->role ?? null) === 'admin' && !$isPrintMode)
                @php
                    $adminEdit = false;
                    $quoteEditUrl = route('admin.orders.show', $order) . '?type=quote';
                @endphp

                <div class="d-flex justify-content-end gap-2 mb-2">
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.quotes.index') }}">
                        <i class="bi bi-arrow-left me-1"></i>Quay lại
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i>In báo giá
                    </button>

                    <a class="btn btn-outline-danger btn-sm" href="{{ route('orders.quote.pdf', ['orderCode' => $orderCode]) }}">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Xuất PDF
                    </a>

                </div>

                @if($adminEdit)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="fw-bold mb-3">Thông tin báo giá</h5>

                            <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">Mã số thuế</label>
                                        <input type="text" name="customer_tax_code" class="form-control" value="{{ old('customer_tax_code', $order->customer_tax_code) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Email</label>
                                        <input type="text" name="customer_email" class="form-control" value="{{ old('customer_email', $order->customer_email) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Người liên hệ (Att)</label>
                                        <input type="text" name="customer_contact_person" class="form-control" value="{{ old('customer_contact_person', $order->customer_contact_person) }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">SĐT liên hệ</label>
                                        <input type="text" name="customer_phone" class="form-control" value="{{ old('customer_phone', $order->customer_phone) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Mã nhân viên (Staff code)</label>
                                        <input type="text" name="staff_code" class="form-control" value="{{ old('staff_code', $order->staff_code) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Báo giá được làm bởi (Sales)</label>
                                        <input type="text" name="sales_name" class="form-control" value="{{ old('sales_name', $order->sales_name) }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Ghi chú</label>
                                        <textarea name="note" class="form-control" rows="2">{{ old('note', $order->note) }}</textarea>
                                    </div>

                                    <div class="col-12 d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary">Lưu thông tin báo giá</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            @endif
        </div>

        <div class="quote-a4-paper">
            <div class="quote-a4-pad">
                <div class="br-header-wrap">
                    <table class="br-header">
                        <tr>
                            <td class="br-header-left">
                                <img src="{{ $webLogo1 }}" alt="Vigilance">
                            </td>
                            <td class="br-header-right">
                                <img src="{{ $webLogo2 }}" alt="">
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
                            <th style="width:32px;">No.</th>
                            <th style="width:250px;">Thông tin chi tiết<br><span class="sub">Details/Description</span></th>
                            <th style="width:60px;">Hình ảnh<br><span class="sub">Product Image</span></th>
                            <th style="width:60px;">Bảo hành<br><span class="sub">Warranty</span></th>
                            <th style="width:46px;">SL<br><span class="sub">Quantity</span></th>
                            <th style="width:70px;">Đơn giá<br><span class="sub">Units Price</span></th>
                            <th style="width:70px;">Thuế (VAT)<br><span class="sub">VAT {{ (int) $vatPercent }}%</span></th>
                            <th style="width:85px;">Trọng tiền<br><span class="sub">Amount</span></th>
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
                                $rawDescription = $item->product->description
                                    ?? $item->product->information
                                    ?? $item->product->specifications
                                    ?? '';
                                $productDescription = trim(strip_tags((string) $rawDescription));
                                $price = (float) ($item->price ?? 0);
                                $qty = (int) ($item->quantity ?? 0);
                                $unit = $item->unit ?? '';
                                $lineSub = $price * $qty;
                                $lineTax = $lineSub * ($vatPercent / 100);
                                $lineAmount = $lineSub + $lineTax;

                                $totalBeforeTax += $lineSub;
                                $totalTax += $lineTax;
                                $totalAmount += $lineAmount;
                            @endphp
                            <tr>
                                <td class="center">{{ $idx + 1 }}</td>
                                <td class="left">
                                    <div><b>{{ $name }}</b></div>
                                    @if($productDescription !== '')
                                        <div class="muted" style="margin-top:2px;">{{ \Illuminate\Support\Str::limit($productDescription, 220) }}</div>
                                    @endif
                                </td>
                                <td class="center">
                                    @if(!empty($productImage))
                                        <img class="q-prod-img" src="{{ asset('images/products/' . $productImage) }}" alt="{{ $name }}">
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
                            $amountInWords = ucfirst(vn_read_number((int) round($totalAmount))) . ' đồng';
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
                                <div class="q-terms">
                                    <div>Lưu ý / Note: Giá trên không bao gồm các chi phí vật tư phụ &amp; công lắp đặt; chưa tính phụ phí làm thêm thứ 7, chủ nhật và các ngày lễ.</div>
                                    <div class="muted">(The above price does not include the cost of additional materials &amp; installation labor on Saturdays, Sundays and public holidays.)</div>
                                </div>
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
                        <li>- Đối với trường hợp ngoại tỉnh, khác khu vực Tp HCM khách hàng tự chọn đơn vị vận chuyển, và  Vigilance sẽ giao hàng tại kho hàng Tp HCM (Chi phí này người mua sẽ tự thoả thuận với đơn vị vận chuyển hoặc liên hệ nhân viên kinh doanh để được hỗ trợ.)</li>
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
                                | Website: <a href="{{ $sellerWebsite }}" target="_blank" rel="noopener noreferrer">{{ $sellerWebsiteLabel }}</a>
                            </td>
                            <td class="q-footer-page">1 / 1</td>
                        </tr>
                    </table>
                </div>

                <div class="no-print mt-3">
                    @php
                        $isAdminViewer = auth()->check() && (auth()->user()->role ?? null) === 'admin';
                    @endphp
                    @if($order->status === 'pending' && !$isAdminViewer)
                        <form method="POST" action="{{ route('orders.quote.confirm', ['orderCode' => $orderCode]) }}" class="misa-form" id="userConfirmQuoteForm">
                            @csrf
                            <div class="misa-form__head">
                                <div class="misa-form__title">Thông tin xác nhận đơn hàng (Form 1)</div>
                                <div class="misa-form__sub">Vui lòng chọn điều khoản và phương thức thanh toán trước khi xác nhận.</div>
                            </div>

                            <div class="misa-form__body">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="misa-label">Điều khoản thanh toán</div>
                                        <div class="misa-choice-group">
                                            <label class="misa-choice">
                                                <input type="radio" name="payment_term" value="full_advance" {{ $selectedPaymentTerm === 'full_advance' ? 'checked' : '' }}>
                                                <span>Thanh toán 100% trước giao hàng</span>
                                            </label>
                                            <label class="misa-choice">
                                                <input type="radio" name="payment_term" value="deposit" {{ $selectedPaymentTerm === 'deposit' ? 'checked' : '' }}>
                                                <span>Đặt cọc + phần còn lại</span>
                                            </label>
                                            <label class="misa-choice">
                                                <input type="radio" name="payment_term" value="debt" {{ $selectedPaymentTerm === 'debt' ? 'checked' : '' }}>
                                                <span>Công nợ theo hạn</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-4" id="userDepositPercentWrap" style="display:none;">
                                        <label class="misa-label" for="userDepositPercent">Tỷ lệ đặt cọc (%)</label>
                                        <input id="userDepositPercent" type="number" min="0.01" max="100" step="0.01" name="deposit_percent" class="form-control misa-control" value="{{ $selectedDepositPercent }}" placeholder="VD: 30">
                                    </div>

                                    <div class="col-md-4" id="userPaymentDueDaysWrap" style="display:none;">
                                        <label class="misa-label" for="userPaymentDueDays">Hạn công nợ (ngày)</label>
                                        <input id="userPaymentDueDays" type="number" min="1" max="3650" name="payment_due_days" class="form-control misa-control" value="{{ $selectedPaymentDueDays }}" placeholder="VD: 30">
                                    </div>

                                    <div class="col-md-4">
                                        <div class="misa-label">Phương thức thanh toán</div>
                                        <select name="payment_method" class="form-select misa-control">
                                            <option value="bank_transfer" {{ $selectedPaymentMethod === 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản</option>
                                            <option value="cash" {{ $selectedPaymentMethod === 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                                            <option value="mixed" {{ $selectedPaymentMethod === 'mixed' ? 'selected' : '' }}>Kết hợp</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="misa-label" for="userPaymentNote">Ghi chú thanh toán</label>
                                        <textarea id="userPaymentNote" name="payment_note" class="form-control misa-control" rows="2" placeholder="Ghi chú thêm (nếu có)">{{ $selectedPaymentNote }}</textarea>
                                        <div class="misa-inline-note">Ví dụ: thanh toán phần còn lại sau nghiệm thu.</div>
                                    </div>

                                    <div class="col-12 misa-actions">
                                        <button type="submit" class="btn btn-success misa-submit">Xác nhận đặt hàng</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const form = document.getElementById('userConfirmQuoteForm');
    if (!form) return;

    const termInputs = form.querySelectorAll('input[name="payment_term"]');
    const depositWrap = document.getElementById('userDepositPercentWrap');
    const debtWrap = document.getElementById('userPaymentDueDaysWrap');
    const depositInput = document.getElementById('userDepositPercent');
    const debtInput = document.getElementById('userPaymentDueDays');

    function getSelectedTerm() {
        const checked = form.querySelector('input[name="payment_term"]:checked');
        return checked ? checked.value : 'full_advance';
    }

    function toggleFields() {
        const term = getSelectedTerm();

        if (term === 'deposit') {
            depositWrap.style.display = '';
            debtWrap.style.display = 'none';
            if (debtInput) debtInput.value = '';
        } else if (term === 'debt') {
            depositWrap.style.display = 'none';
            debtWrap.style.display = '';
            if (depositInput) depositInput.value = '';
        } else {
            depositWrap.style.display = 'none';
            debtWrap.style.display = 'none';
            if (depositInput) depositInput.value = '';
            if (debtInput) debtInput.value = '';
        }
    }

    termInputs.forEach(function (input) {
        input.addEventListener('change', toggleFields);
    });

    toggleFields();
})();
</script>

@if($isPrintMode)
<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 150);
    });
</script>
@endif
@endsection
