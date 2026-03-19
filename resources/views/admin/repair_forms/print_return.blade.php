<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Trả - {{ $repairForm->form_number }}</title>
    <style>
        @page { size: A4; margin: 10mm; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Times New Roman", Times, serif;
            color: #000;
            background: #fff;
            line-height: 1.35;
            font-size: 13px;
        }
        .sheet {
            max-width: 190mm;
            margin: 0 auto;
        }
        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 8mm;
        }
        .header-left { width: 55mm; }
        .logo {
            width: 38mm;
            height: auto;
        }
        .header-mid {
            flex: 1;
            display: flex;
            justify-content: center;
        }
        .header-right {
            width: 55mm;
            text-align: right;
            font-size: 13px;
            margin-top: 15mm;
            white-space: nowrap;
            font-style: normal;
        }
        .title {
            text-align: center;
            font-weight: 700;
            margin-top: 2mm;
            line-height: 1.25;
        }
        .title .l1 { font-size: 18px; }
        .title .l2 { font-size: 16px; margin-top: 2px; }
      
        
        table.form {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 13px;
        }
        table.form td, table.form th {
            border: 1px solid #000;
            padding: 4px 6px;
            
        }
        .subtable {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .subtable td { border: none !important; padding: 3px 0; vertical-align: top; }
        .subtable .col-left { width: 35%; }
        .subtable .col-right { width: 35%;  white-space: nowrap; }
        .section {
            font-weight: 700;
            background: #fff;
            vertical-align: middle;
            line-height: 1.15;
            padding-bottom: 5px;
            line-height: 1.2;
        }
        .cb {
            display: inline-block;
            width: 4mm;
            height: 4mm;
            border: 1px solid #000;
            position: relative;
            line-height: 4mm;
            text-align: center;
            vertical-align: middle;
            margin-left: 2mm;
            margin-right: 2mm;
        }
        .cb.checked {
            background: #fff;
        }
        .cb.checked::after {
            content: "✓";
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -56%);
            font-size: 14px;
            font-weight: 700;
            color: #000;
            line-height: 1;
        }
        .center { text-align: center; }
        .fill { font-weight: 700; }
        .sig td { height: 45mm; vertical-align: top; }
        .name { margin-top: 30mm; font-weight: 700; }
        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 10px;
        }
        .btn {
            border: 1px solid #000;
            padding: 8px 10px;
            background: #fff;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
        }
        .policy {
            margin-top: 6mm;
            font-size: 13px;
            line-height: 1.35;
        }
        .policy .head {
            font-weight: 700;
            text-align: center;
            margin-bottom: 3mm;
        }
        .policy p {
            margin: 0 0 2mm 0;
        }
        .policy ul {
            margin: 0 0 2mm 0;
            padding-left: 5mm;
        }
        .policy li {
            margin: 0 0 1.5mm 0;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <div class="header-left">
                <img class="logo" src="{{ asset('logovigilance.jpg') }}" alt="Logo">
            </div>
            <div class="header-mid">
                <div class="title">
                    <div class="l1">PHIẾU TRẢ</div>
                    <div class="l2">THIẾT BỊ / SẢN PHẨM</div>
                </div>
            </div>
            <div class="header-right">
                <span style="font-style: italic;">TP.HCM, Ngày {{ ($repairForm->actual_return_date ?: $repairForm->created_at)->format('j') }} Tháng {{ ($repairForm->actual_return_date ?: $repairForm->created_at)->format('n') }} Năm {{ ($repairForm->actual_return_date ?: $repairForm->created_at)->format('Y') }}</span>
            </div>
        </div>

        <div class="box">
            <table class="form">
                <tr>
                    <td colspan="2" class="section">THÔNG TIN KHÁCH HÀNG</td>
                </tr>
                <tr>
                    <td colspan="2">Tên khách hàng: <span class="fill">{{ mb_strtoupper($repairForm->customer_company ?? '', 'UTF-8') }}</span></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <table class="subtable">
                            <tr>
                                <td class="col-left" style="padding-left:0;">Người liên hệ: <span class="fill">{{ $repairForm->contact_person ?: '' }}</span></td>
                                <td class="col-right">Số điện thoại: <span class="fill">{{ $repairForm->contact_phone }}</span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <table class="subtable">
                            <tr>
                                <td class="col-left" style="padding-left:0;">Người tiếp nhận: <span class="fill">{{ $repairForm->received_by ?: '' }}</span></td>
                                <td class="col-right">SĐT người tiếp nhận: <span class="fill">{{ $repairForm->received_by_phone ?: '' }}</span></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">Ngày mua hàng: <span class="fill">{{ $repairForm->purchase_date ? $repairForm->purchase_date->format('d/m/Y') : '' }}</span></td>
                </tr>

                <tr>
                    <td class="section" style="width:58%;">THÔNG TIN SẢN PHẨM</td>
                    <td class="section" style="width:58%;">THÔNG TIN BÀN GIAO</td>
                </tr>
                <tr>
                    <td>Tên thiết bị(*): <span class="fill">{{ $repairForm->equipment_name }}</span></td>
                    <td>Ngày bàn giao: <span class="fill">{{ $repairForm->actual_return_date ? $repairForm->actual_return_date->format('d/m/Y') : '' }}</span></td>
                </tr>
                <tr>
                    <td>
                        Tình trạng báo lỗi: <span class="fill">{{ $repairForm->error_status }}</span>
                        <br>
                        Thời gian bảo hành/ sửa chữa dự kiến: <span class="fill">{{ $repairForm->repair_time_required ?: '' }}</span>
                    </td>
                    <td>
                        Phụ kiện bàn giao: <span class="fill">{{ $repairForm->accessories ?: '' }}</span>
                    </td>
                </tr>

                <tr>
                    <td>
                        Serial No.*: <span class="fill">{{ $repairForm->serial_numbers }}</span>
                        <br>
                        Ngày tiếp nhận: <span class="fill">{{ $repairForm->received_date ? $repairForm->received_date->format('d/m/Y') : '' }}</span>
                    </td>
                    <td>
                        Thông tin đã sửa chữa: <span class="fill">{{ $repairForm->handover_repair_info ?: '' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>
                        Còn bảo hành <span class="cb {{ $repairForm->warranty_status === 'under_warranty' ? 'checked' : '' }}"></span>
                        <span style="display:inline-block; width: 10mm;"></span>
                        Hết bảo hành <span class="cb {{ $repairForm->warranty_status === 'out_of_warranty' ? 'checked' : '' }}"></span>
                    </td>
                    <td>
                        Thời gian bảo hành/sửa chữa: <span class="fill">{{ $repairForm->handover_check_info ?: '' }}</span>
                    </td>
                </tr>

                <tr class="sig">
                    <td style="width: 50%;" class="center">
                        <div style="font-weight:700;">Khách hàng nhận</div>
                        <div class="name">{{ $repairForm->contact_person ?: '' }}</div>
                    </td>
                    <td style="width: 50%;" class="center">
                        <div style="font-weight:700;">Người bàn giao</div>
                        <div class="name">{{ $repairForm->handed_over_by ?: ($repairForm->received_by ?: 'Bùi Nguyễn Tường Vy') }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="policy">
            <div class="head">ĐIỀU KIỆN BẢO HÀNH &amp; SỬA CHỮA </div>

            <p>Vigilance áp dụng bảo hành phần cứng 01 năm cho các sản phẩm do Vigilance phân phối, tính từ ngày xuất hóa đơn hoặc 30 ngày kể từ ngày giao hàng (tùy mốc nào đến trước). Bảo hành chỉ áp dụng cho các lỗi kỹ thuật phát sinh từ nhà sản xuất liên quan đến vật liệu và tay nghề.</p>

            <p>Trong thời gian bảo hành, Vigilance sẽ kiểm tra, sửa chữa hoặc thay thế sản phẩm đủ điều kiện bảo hành theo đúng tiêu chuẩn kỹ thuật và quy trình của nhà sản xuất. Khách hàng có trách nhiệm lắp đặt, sử dụng và bảo quản sản phẩm đúng hướng dẫn, môi trường vận hành phù hợp và không can thiệp trái phép vào thiết bị.</p>

            <p>Bảo hành không áp dụng đối với các trường hợp: sản phẩm bị sửa đổi, tháo lắp hoặc can thiệp kỹ thuật khi chưa được sự chấp thuận bằng văn bản của Vigilance; mất, rách hoặc thay đổi số serial; hư hỏng do nước, sét, điện áp bất thường, tai nạn, tác động vật lý hoặc sử dụng sai mục đích, sai hướng dẫn của nhà sản xuất.</p>

            <p>Khi gửi sản phẩm bảo hành, khách hàng phải cung cấp phiếu/báo cáo lỗi, mô tả rõ tình trạng sự cố. Sản phẩm phải được vận chuyển về trung tâm Vigilance với chi phí vận chuyển và bảo hiểm do khách hàng chịu. Vigilance không chịu trách nhiệm đối với các rủi ro phát sinh trong quá trình vận chuyển hoặc các thiệt hại gián tiếp như mất dữ liệu, gián đoạn kinh doanh, mất doanh thu.</p>


            <p>Đối với các sản phẩm đã hết thời hạn bảo hành, Vigilance vẫn hỗ trợ kiểm tra và sửa chữa theo yêu cầu của khách hàng. Phí kiểm tra tiêu chuẩn là <strong>162.000 VNĐ/máy (đã bao gồm VAT)</strong>, chưa bao gồm chi phí linh kiện thay thế (nếu có).</p>

            <p>Mọi linh kiện được thay thế trong quá trình sửa chữa ngoài bảo hành sẽ được bảo hành <strong>03 tháng</strong> kể từ ngày bàn giao thiết bị sau sửa chữa, với điều kiện sử dụng đúng kỹ thuật và không phát sinh các nguyên nhân loại trừ bảo hành nêu trên.</p>
        </div>

        <div class="actions no-print">
            <button class="btn" onclick="window.history.back()">Quay lại</button>
            <button class="btn" onclick="window.print()">In phiếu</button>
        </div>
    </div>
</body>
</html>
