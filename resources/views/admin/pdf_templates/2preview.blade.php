<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Preview Mẫu 2</title>
    <style>
        @page { size: A4; margin: 10mm 11mm 8mm 11mm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        .page { width: 100%; position: relative; min-height: 1020px; padding-bottom: 95px; }
        .top { display: table; width: 100%; }
        .top-left, .top-right { display: table-cell; vertical-align: top; }
        .top-left { width: 58%; }
        .top-right { width: 42%; text-align: right; }
        .logo { width: 145px; margin-top: 0; display: block; }
        .title { color: #ff7a7a; font-size: 26px; font-weight: 800; line-height: 1; margin-top: 2px; }
        .meta { margin-top: 4px; font-size: 10.5px; color: #666; line-height: 1.35; }
        .line { border-top: 1px solid #ffb3b3; margin: 8px 0 10px; }
        .label { font-size: 14px; font-weight: 700; }
        .customer { margin-top: 6px; }
        .customer div { margin: 1px 0; }
        .intro { margin: 8px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        .items th, .items td { border: 1px solid #222; padding: 5px 5px; vertical-align: middle; font-size: 10.5px; }
        .items th { text-align: center; font-weight: 700; background: #fff; }
        .t-center { text-align: center; }
        .t-right { text-align: right; }
        .img-cell img { max-width: 52px; max-height: 52px; object-fit: contain; display: block; margin: 0 auto; }
        .content { display: table; width: 100%; margin-top: 8px; }
        .content-left, .content-right { display: table-cell; vertical-align: top; }
        .content-left { width: 56%; padding-right: 8px; }
        .content-right { width: 44%; }
        .notes { font-size: 10.4px; line-height: 1.42; }
        .notes p { margin: 0 0 6px; }
        .summary { width: 100%; border-collapse: collapse; margin-top: 0; }
        .summary td { padding: 1px 0; font-size: 11px; }
        .sum-label { font-weight: 700; white-space: nowrap; }
        .sum-value { text-align: right; font-weight: 700; white-space: nowrap; }
        .amount-words { padding-top: 4px !important; }
        .signature { margin-top: 10px; margin-left: auto; margin-right: 66px; width: 220px; text-align: center; font-size: 11px; line-height: 1.35; }
        .signature .signed { margin: 1px 0; }
        .signature .name { margin-top: 2px; font-weight: 700; }
        .signature .role { margin-top: 0; }
        .block-wide { margin-top: 10px; font-size: 10.5px; line-height: 1.38; }
        .block-wide p { margin: 0 0 5px; }
        .center { text-align: center; }
        .red { color: #f02121; font-weight: 700; }
        .italic { font-style: italic; }
        .bank { margin-top: 6px; }
        .bank p { margin: 0 0 4px; }
        .footer { border-top: 1px solid #f1c5c5; margin-top: 18px; padding-top: 7px; }
        .footer-company { color: #ff7a7a; font-size: 10px; font-weight: 700; margin-bottom: 4px; }
        .footer-line { font-size: 9.2px; color: #6b7280; line-height: 1.3; }
        .footer-meta { display: table; width: 100%; margin-top: 3px; font-size: 9.2px; color: #6b7280; }
        .footer-meta div { display: table-cell; vertical-align: top; }
        .footer-page { text-align: right; white-space: nowrap; }
    </style>
</head>
<body>
<div class="page">
    <div class="top">
        <div class="top-left"><img src="{{ public_path('logo1.png') }}" class="logo" alt="logo"></div>
        <div class="top-right">
            <div class="title">BÁO GIÁ</div>
            <div class="meta">Số: {{ $quote->quote_code ?? '' }}<br>Ngày: {{ optional($quote->created_at)->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="line"></div>

    <div class="label">Kính gửi:</div>
    <div class="customer">
        <div><b>Tên khách hàng:</b> {{ $customerName }}</div>
        <div><b>Địa chỉ/Address:</b> {{ $address }}</div>
        <div><b>Mã số thuế/Tax code:</b> {{ $taxCode }}</div>
        <div><b>Điện thoại/Tel:</b> {{ $phone }}</div>
        <div><b>Email:</b> {{ $email }}</div>
    </div>

    <div class="intro">Theo yêu cầu của Quý khách, Đại diện Vigilance VN xin gửi thông tin chi tiết và báo giá các sản phẩm, dịch vụ dưới đây:</div>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 38px;">STT</th>
                <th>Tên hàng hóa / DV và chi tiết sản phẩm</th>
                <th style="width: 76px;">Số lượng</th>
                <th style="width: 100px;">Hình ảnh sản phẩm</th>
                <th style="width: 82px;">Đơn giá</th>
                <th style="width: 56px;">VAT</th>
                <th style="width: 82px;">Tiền thuế</th>
                <th style="width: 92px;">Sau thuế</th>
            </tr>
        </thead>
        <tbody>{!! $itemRows !!}</tbody>
    </table>

    <div class="content">
        <div class="content-left notes">
            <p>- <b>Phương thức thanh toán:</b> {{ $paymentTermLabel ?? '---' }}</p>
            @if(($quote->payment_term ?? 'full_advance') === 'deposit')
            <p>- <b>Tỷ lệ đặt cọc:</b> {{ !empty($depositPercent) ? ($depositPercent . '%') : '---' }}</p>
            <p>- <b>Ghi chú thanh toán:</b> {{ !empty($paymentNote) ? $paymentNote : '---' }}</p>
            @elseif(($quote->payment_term ?? 'full_advance') === 'debt')
            <p>- <b>Hạn công nợ:</b> {{ !empty($paymentDueDays) && $paymentDueDays !== '0' ? ($paymentDueDays . ' ngày') : '---' }}</p>
            @if(!empty($paymentNote))
            <p>- <b>Ghi chú thanh toán:</b> {{ $paymentNote }}</p>
            @endif
            @endif
            <p>Miễn phí giao hàng cho đơn hàng trên 5 triệu đồng và chi phí giao hàng không vượt quá 50,000 VNĐ/ một đơn hàng (<= 10km)</p>
            <p>Giao hàng ngay sau khi thanh toán, nếu trong ngày trước 15:00, sau 15:00 sẽ dời sang ngày hôm sau.</p>
            <p>Báo giá này chỉ có hiệu lực trong vòng 15 ngày kể từ ngày báo giá.</p>
        </div>


    <div class="block-wide" style="margin-top: 8px;">
        <p class="center red italic">TẶNG PHẦN MỀM VKSOFTWARE DÙNG CHO SẢN PHẨM:</p>
        <p class="center italic">ZKTECO, TIMMY, RONALDJACK, ABRIVISION, HIKVISION, DAHUA, VIGILANCE, MITA, WISEEYE…</p>
    </div>

    <div class="bank block-wide" style="margin-top: 4px;">
        <p><b>- Tài khoản ngân hàng:</b></p>
        <p style="margin-left: 18px;">Tên đơn vị thụ hưởng: Công ty Cổ phần Vigilance Việt Nam</p>
        <p style="margin-left: 18px;">Số tài khoản ACB: 261223888 - Ngân hàng TMCP Á Châu - Chi nhánh Sài Gòn</p>
        <p>- Sản phẩm được bảo hành chính hãng: 12 tháng tính từ ngày giao hàng/theo hóa đơn tài chính tùy theo điều kiện nào tới trước.</p>
    </div>

    <div class="signature" style="margin-top: 14px;">
        <div><b>Đại diện Vigilance Việt Nam</b></div>
        <div class="signed">(Đã ký)</div>
        <div class="name">LÊ THỊ PHI NGA</div>
        <div class="role">(Giám đốc)</div>
    </div>

    <div class="footer" style="position: absolute; left: 0; right: 0; bottom: 0;">
        <div class="footer-company">CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</div>
        <div class="footer-line">Địa chỉ: Phòng B15.09 Tầng 15, Tháp B Tòa nhà Rivergate 151-155 Bến Vân Đồn, Phường Khánh Hội, TP.HCM</div>
        <div class="footer-meta">
            <div>Mã số thuế: 0318231312 | Hotline: 0982751075 | Email: <a href="#">vigilancevn@gmail.com</a> | Website: <a href="#">www.vigilancevn.com.vn</a></div>
            <div class="footer-page">1 / 1</div>
        </div>
    </div>
</div>
</body>
</html>
