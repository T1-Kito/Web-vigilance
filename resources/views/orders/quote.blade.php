@extends('layouts.user')

@section('title', 'Báo giá')

@section('content')
@php
    $discountPercent = (float) ($order->discount_percent ?? 0);
    $vatPercent = (float) ($order->vat_percent ?? 8);

    $items = $order->items ?? collect();

    $orderCode = $order->order_code ?? ("VK" . str_pad($order->id, 6, '0', STR_PAD_LEFT));

    $companyName = $order->company_name ?: 'CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM';
    $companyTax = $order->company_tax_code ?: '0318231312';
    $companyHotline = $order->company_hotline ?: '02873026078';
    $companyAddressRaw = $order->company_address ?: '151-155 Bến Vân Đồn, Phường Khánh Hội, TP HCM';
    $companyAddressSafe = str_replace('151-155', '<span style="white-space:nowrap;">151-155</span>', e($companyAddressRaw));

    $invoiceName = $order->invoice_company_name ?: '...';
    $invoiceAddress = $order->invoice_address ?: '...';
    $receiverPhone = $order->customer_phone ?: $order->receiver_phone;
    $receiverEmail = $order->customer_email ?: '...';
    $receiverTax = $order->customer_tax_code ?: '...';
    $receiverAtt = $order->customer_contact_person ?: '...';

    $staffCode = $order->staff_code ?: ($order->user->name ?? '...');
    $salesName = $order->sales_name ?: ($order->user->name ?? '...');

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

    $amountInWords = ucfirst(vn_read_number((int) round($totalAmount))) . ' đồng';
@endphp

<style>
    .quote-a4-wrap { max-width: 980px; margin: 0 auto; }
    .quote-a4-paper { background: #fff; }
    .quote-a4-pad { padding: 14px 16px; }

    .q-top { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .q-logo { display: flex; align-items: flex-start; gap: 12px; }
    .q-logo img { max-width: 300px; height: auto; }

    .q-company-right { text-align: right; }
    .q-company-right .q-company-name { color: #d32f2f; font-weight: 500; font-size: 13px; text-transform: uppercase; }
    .q-company-right .q-company-meta { font-size: 12px; }

    .q-block { border-top: 1px solid #999; border-bottom: 1px solid #999; padding: 8px 0; }
    .q-block-grid { display: grid; grid-template-columns: 1fr 1fr; column-gap: 0; }
    .q-block-grid > div:first-child { padding-right: 12px; }
    .q-block-grid > .q-col-right { border-left: 1px solid #535252; padding-left: 12px; }
    .q-line { font-size: 12px; line-height: 1.25; }
    .q-label { font-weight: 500; }

    .q-center-title { text-align: center; margin: 10px 0 6px; }
    .q-center-title .t1 { font-weight: 500; font-size: 18px; letter-spacing: 0.4px; }
    .q-center-title .t2 { font-style: italic; font-weight: 800; font-size: 13px; margin-top: 2px; }

    .q-redbar { background: #d32f2f; color: #fff; font-weight: 500; text-transform: uppercase; font-size: 12px; padding: 6px 8px; border: 1px solid #999; border-bottom: none; }

    .q-table { width: 100%; border-collapse: collapse; font-size: 11px; }
    .q-table th, .q-table td { border: 1px solid #999; padding: 4px 5px; }
    .q-table thead th { font-weight: 500; text-align: center; }
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

    .q-out-terms { font-size: 10.5px; line-height: 1.5; margin-top: 12px; }
    .q-out-terms .sep { border-top: 2px solid #999; margin: 12px 0; }
    .q-out-terms ul { margin: 0 0 0 18px; padding: 0; }
    .q-out-terms li { margin: 4px 0; }

    .q-accept { text-align: center; font-size: 10.5px; margin-top: 10px; }
    .q-accept .sigline { margin-top: 26px; }

    .q-footer { display: flex; justify-content: space-between; align-items: center; font-size: 10px; margin-top: 18px; }
    .q-footer .mid { color: #d32f2f; font-weight: 900; }

    .no-print { display: block; }

    .admin-qe { border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.06); }
    .admin-qe .admin-qe-head { padding: 10px 12px; background: linear-gradient(180deg, #fafafa, #ffffff); border-bottom: 1px solid #eef0f3; }
    .admin-qe .admin-qe-title { font-weight: 800; font-size: 13px; }
    .admin-qe .admin-qe-code { font-size: 12px; color: #6b7280; font-weight: 600; }
    .admin-qe .admin-qe-body { padding: 12px; }
    .admin-qe .admin-qe-label { font-size: 11.5px; color: #374151; font-weight: 700; margin-bottom: 4px; }
    .admin-qe .admin-qe-control { font-size: 13px; padding: 8px 10px; border-radius: 10px; border: 1px solid #e5e7eb; background: #fff; }
    .admin-qe .admin-qe-control:focus { border-color: #2563eb; box-shadow: 0 0 0 .18rem rgba(37, 99, 235, .14); }
    .admin-qe .admin-qe-actions { display: flex; align-items: flex-end; justify-content: flex-end; }
    .admin-qe .admin-qe-submit { padding: 9px 16px; border-radius: 10px; font-weight: 800; min-width: 110px; }
    .admin-qe textarea.admin-qe-control { line-height: 1.35; max-height: 44px; resize: vertical; }
    .admin-qe .row.g-2 { --bs-gutter-x: .6rem; --bs-gutter-y: .6rem; }
    @media (max-width: 768px) {
        .admin-qe .admin-qe-actions { justify-content: stretch; }
        .admin-qe .admin-qe-submit { width: 100%; }
    }

    @page { size: A4; margin: 8mm; }
    @media print {
        .no-print { display: none !important; }
        .container, .quote-a4-wrap { max-width: none !important; padding: 0 !important; margin: 0 !important; }
        .quote-a4-paper { border: none !important; }
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

            @if(auth()->check() && (auth()->user()->role ?? null) === 'admin')
                @php
                    $adminEdit = (string) request()->query('admin_edit') === '1';
                    $isEmbed = (string) request()->query('embed') === '1';
                    $quoteUrl = route('orders.quote', ['orderCode' => $order->order_code]);
                    $quoteEditUrl = $quoteUrl . ($isEmbed ? '?embed=1&admin_edit=1' : '?admin_edit=1');
                    $quoteViewUrl = $quoteUrl . ($isEmbed ? '?embed=1' : '');
                @endphp

                <div class="d-flex justify-content-end gap-2 mb-2">
                    @if($adminEdit)
                        <a class="btn btn-outline-secondary btn-sm" href="{{ $quoteViewUrl }}">Tắt chỉnh sửa</a>
                    @else
                        <a class="btn btn-primary btn-sm" href="{{ $quoteEditUrl }}">Mở chỉnh sửa</a>
                    @endif
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
                <div class="q-top">
                    <div class="q-logo">
                        <img src="{{ asset('images/vigilance-logo.png') }}" alt="Vigilance">
                    </div>
                    <div class="q-company-right">
                        <div class="q-company-name">{{ $companyName }}</div>
                        <div class="q-company-meta">
                            MST: {{ $companyTax }}<br>
                            Địa chỉ: {!! $companyAddressSafe !!}<br>
                            Hotline: {{ $companyHotline }}
                        </div>
                    </div>
                </div>
 
                <div class="q-block mt-2">
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
                                <td class="left">{{ $name }}</td>
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

                    <div><b>Bảo hành (Warranty):</b> Tất cả sản phẩm do Vigilance Việt Nam phân phối đều được tiếp nhận và bảo hành tại trụ sở chính của công ty: G4, Cư xá Vĩnh Hội, Phường Khánh Hội, Tp HCM.</div>

                    <div class="sep"></div>

                    <div>- Tài khoản ngân hàng/ Account Bank:</div>
                    <div>&nbsp;&nbsp;&nbsp;&nbsp;Tên đơn vị thụ hưởng: {{ $companyName }}</div>
                    <div>&nbsp;&nbsp;&nbsp;&nbsp;Số tài khoản ACB (ACB Account): 261223888 - Ngân hàng TMCP Á Châu - Chi nhánh Sài Gòn</div>
                    <div class="muted"><b>(Xin lưu ý chúng tôi chỉ sử dụng 01 tài khoản tên với tên doanh nghiệp, mọi tài khoản doanh nghiệp khác đều không được chấp nhận thanh toán)</b></div>

                    <div class="sep"></div>

                    <div><b>Những sản phẩm không liệt kê trong danh mục trong báo giá này đều xem là chi phí phát sinh./Products not listed in the list on this quote are considered incurred costs.</b></div>

                    <div class="sep"></div>

                    <div style="text-align:center;">
                        <div>Đồng ý &amp; Xác nhận bởi (Accepted by):</div>
                        <div>Customer's Chop &amp; Signature (Ký tên và đóng dấu)</div>
                    </div>
                </div>

                <div class="q-footer">
                    <div>{{ optional($order->created_at)->format('d/m/Y') }} - Mẫu BG{{ $order->id }}</div>
                    <div class="mid">Vigilance - {{ $companyHotline }}</div>
                    <div>Báo giá -Trang 1</div>
                </div>

                <div class="no-print mt-3 text-center">
                    @if($order->status === 'pending')
                        <form method="POST" action="{{ route('orders.quote.confirm', ['orderCode' => $order->order_code]) }}">
                            @csrf
                            <button type="submit" class="btn btn-success px-4">Xác nhận đặt hàng</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
