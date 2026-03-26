@extends('layouts.admin')

@section('title', 'Chi tiết phiếu mượn hàng')

@section('content')
@php
    $st = $borrowRequest->display_status;
    $stLabel = $statusOptions[$st] ?? $st;
    $badgeStyle = 'background: linear-gradient(135deg, #6b7280, #4b5563);';
    if ($st === 'proposed') $badgeStyle = 'background: linear-gradient(135deg, #f59e0b, #d97706);';
    if ($st === 'processing') $badgeStyle = 'background: linear-gradient(135deg, #3b82f6, #1d4ed8);';
    if ($st === 'borrowing') $badgeStyle = 'background: linear-gradient(135deg, #8b5cf6, #6d28d9);';
    if ($st === 'returned') $badgeStyle = 'background: linear-gradient(135deg, #10b981, #059669);';
    if ($st === 'overdue') $badgeStyle = 'background: linear-gradient(135deg, #ef4444, #dc2626);';

    $requestedByDisplay = $borrowRequest->requested_by_name ?: (optional($borrowRequest->requestedByAdmin)->name ?: 'Admin');
    $approvedByDisplay = $borrowRequest->approved_by_name ?: '';
@endphp

<div class="container-fluid py-4">
    <style>
        .br-actions { display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap; }
        .br-form-wrap { background:#fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; }
        .br-a4 { max-width: 794px; margin: 0 auto; color: #111827; background: #fff;padding: 26px 30px; box-shadow: 0 12px 30px rgba(0,0,0,0.08); }
        .br-header { display:flex; justify-content:space-between; gap: 16px; }
        .br-header-left { width: 35%; }
        .br-header-left img { max-width: 170px; height: auto; }
        .br-header-right { width: 65%; text-align: end; line-height: 1.35; font-size: 0.95rem; }
        .br-company-name { font-weight: 800; color: #ef4444; font-size: 1rem; }
        .br-title { text-align:center; font-weight: 700; margin: 18px 0 4px; letter-spacing: 0.5px;font-size: 22px; }
        .br-subtitle { text-align:center; font-size: 0.95rem; margin: 0 0 12px; }
        .br-info { margin-top: 10px; }
        .br-row { display:flex; gap: 16px; flex-wrap: wrap; margin-bottom: 8px; }
        .br-field { width: 100%; display:flex; align-items:flex-end; gap: 10px; }
        .br-field .lbl { flex: 0 0 160px; font-weight: 600; }
        .br-line { flex: 1 1 auto; min-width: 180px; padding: 0 4px 2px; border-bottom: 1px dotted #111827; }
        .br-line.short { flex: 0 0 220px; }
        .br-line.wide { flex: 1 1 420px; }
        .br-muted { color: #374151; font-weight: 500; }
        .br-time { align-items:flex-start; }
        .br-time-left { flex: 0 0 160px; }
        .br-time-left .lbl { display:block; }
        .br-time-left .note { display:block; font-weight: 500; font-size: 0.9em; line-height: 1.2; margin-top: 2px; }
        .br-time-right { flex: 1 1 auto; }
        .br-time-row { display:flex; align-items:flex-end; gap: 10px; margin-bottom: 6px; }
        .br-time-row:last-child { margin-bottom: 0; }
        .br-time-row .t-lbl { flex: 0 0 auto; font-weight: 600; }
        .br-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .br-table tfoot td { font-weight: 700; background: #fff; }
        .br-terms { margin-top: 12px; font-size: 0.95rem; }
        .br-terms .sec { margin-top: 10px; }
        .br-terms .sec .ttl { font-weight: 700; margin-bottom: 4px; }
        .br-terms ul { margin: 6px 0 0 20px; }
        .br-terms li { margin: 4px 0; }
        .br-disclaimer { margin-top: 10px; font-size: 0.95rem; }
        .br-table th, .br-table td { border: 1.2px solid #111827; padding: 10px 8px; font-size: 0.95rem; }
        .br-table th { text-align:center; font-weight: 600; background: #f3f4f6; }
        .br-table td { vertical-align: top; }
        .br-check { display:flex; align-items:center; gap: 8px; margin-top: 6px; }
        .br-box { width: 14px; height: 14px; border: 1.5px solid #111827; display:inline-block; }
        .br-box.checked { background: #111827; box-shadow: inset 0 0 0 2px #fff; }
        .br-sign { display:flex; justify-content:space-between; gap: 16px; margin-top: 26px; }
        .br-sign .side { width: 25%; text-align:center; }
        .br-sign .mid { width: 50%; text-align:center; }
        .br-sign .mid-title { font-weight: 700; margin-bottom: 6px; }
        .br-sign .mid-row { display:flex; justify-content:space-between; gap: 16px; }
        .br-sign .mid-col { width: 50%; text-align:center; }
        .br-sign .cap { font-weight: 700; }
        .br-foot { margin-top: 18px; }

        @media print {
            body * { visibility: hidden !important; }
            .br-form-wrap, .br-form-wrap * { visibility: visible !important; }
            .br-form-wrap { border: none !important; padding: 0 !important; }
            .br-form-wrap { position: absolute !important; left: 0 !important; top: 0 !important; width: 100% !important; }
            .container-fluid { padding: 0 !important; }
            .br-a4 { max-width: none !important; box-shadow: none !important; border: none !important; }
            @page { size: A4; margin: 10mm; }
        }
    </style>

    <div class="mb-4 br-actions">
        <div>
            <h2 class="mb-1">{{ $borrowRequest->code ?: ('Phiếu #' . $borrowRequest->id) }}</h2>
            <div style="display:flex; align-items:center; gap: 10px; flex-wrap: wrap;">
                <span class="badge" style="{{ $badgeStyle }} color:white; border-radius: 20px; padding: 8px 14px; font-weight: 700; font-size: 0.95em;">{{ $stLabel }}</span>
                <span style="color:#6b7280; font-weight:600;">Tạo lúc: {{ optional($borrowRequest->created_at)->format('d/m/Y H:i') }}</span>
            </div>
        </div>
        <div style="display:flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-outline-dark" onclick="window.print()"><i class="bi bi-printer me-1"></i>In</button>
            <a href="{{ route('admin.borrow-requests.edit', $borrowRequest) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i>Sửa</a>
            <a href="{{ route('admin.borrow-requests.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="br-form-wrap">
        <div class="br-a4">
            <div class="br-header">
                <div class="br-header-left">
                    <img src="{{ asset('logovigilance.jpg') }}" alt="Vigilance">
                </div>
                <div class="br-header-right">
                    <div class="br-company-name">CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</div>
                    <div>Địa chỉ: Phòng B15.09 Tầng 15, Tháp B Tòa nhà Rivergate</div>
                    <div>151-155 Bến Vân Đồn, Phường Khánh Hội, TP.HCM</div>
                    <div>Mã số thuế: 0318231312</div>
                    <div>Email : vigilancevn@gmail.com</div>
                </div>
            </div>

            <div class="br-title">PHIẾU ĐỀ NGHỊ MƯỢN HÀNG</div>
            <div class="br-subtitle">(Số: <span class="br-muted">{{ $borrowRequest->code ?: ('Phiếu #' . $borrowRequest->id) }}</span>)</div>

            <div class="br-info">
                <div class="br-row">
                    <div class="br-field">
                        <span class="lbl">Người đề nghị:</span>
                        <span class="br-line wide">{{ $requestedByDisplay }}</span>
                    </div>
                </div>

                <div class="br-row">
                    <div class="br-field">
                        <span class="lbl">Bộ phận:</span>
                        @php $department = $borrowRequest->department ?: ''; @endphp
                        <span class="br-line wide">{{ $department }}</span>
                    </div>
                </div>

                <div class="br-row">
                    <div class="br-field">
                        <span class="lbl">Tên khách hàng:</span>
                        @php $customerName = $borrowRequest->customer_name ?: ''; @endphp
                        <span class="br-line wide">{{ $customerName }}</span>
                    </div>
                </div>

                <div class="br-row">
                    <div class="br-field">
                        <span class="lbl">Người liên hệ:</span>
                        @php $contactName = $borrowRequest->contact_name ?: ''; @endphp
                        <span class="br-line wide">{{ $contactName }}</span>
                    </div>
                </div>

                <div class="br-row">
                    <div class="br-field">
                        <span class="lbl">Mã số thuế:</span>
                        @php $taxCode = $borrowRequest->tax_code ?: ''; @endphp
                        <span class="br-line wide">{{ $taxCode }}</span>
                    </div>
                </div>

                <div class="br-row">
                    <div class="br-field">
                        <span class="lbl">Email:</span>
                        @php $email = $borrowRequest->email ?: ''; @endphp
                        <span class="br-line wide">{{ $email }}</span>
                    </div>
                </div>

                <div class="br-row">
                    <div class="br-field">
                        <span class="lbl">Số điện thoại:</span>
                        @php $contactPhone = $borrowRequest->contact_phone ?: ''; @endphp
                        <span class="br-line wide">{{ $contactPhone }}</span>
                    </div>
                </div>

                <div class="br-row">
                    <div class="br-field">
                        <span class="lbl">Mục đích mượn hàng:</span>
                        @php $purpose = $borrowRequest->purpose ?: ''; @endphp
                        <span class="br-line wide">{{ $purpose }}</span>
                    </div>
                </div>

                <div class="br-row" style="margin-bottom: 6px;">
                    <div class="br-field br-time">
                        <div class="br-time-left">
                            <span class="lbl">Thời gian mượn:</span>
                            <span class="note">(tối đa 07 ngày)</span>
                        </div>
                        <div class="br-time-right">
                            <div class="br-time-row">
                                <span class="t-lbl">Từ ngày:</span>
                                @php $borrowFrom = $borrowRequest->borrow_from ? $borrowRequest->borrow_from->format('d/m/Y') : ''; @endphp
                                <span class="br-line short">{{ $borrowFrom }}</span>
                            </div>
                            <div class="br-time-row">
                                <span class="t-lbl">Đến ngày:</span>
                                @php $borrowTo = $borrowRequest->borrow_to ? $borrowRequest->borrow_to->format('d/m/Y') : ''; @endphp
                                <span class="br-line short">{{ $borrowTo }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <table class="br-table">
                <thead>
                    <tr>
                        <th style="width:7%;">STT</th>
                        <th>Tên hàng</th>
                        <th style="width:12%;">ĐVT</th>
                        <th style="width:13%;">Số lượng</th>
                        <th style="width:17%;">Giá trị</th>
                        <th style="width:18%;">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($borrowRequest->items as $it)
                        <tr style="height: 46px;">
                            <td style="text-align:center; font-weight:700;">{{ $it->line_no }}</td>
                            <td>{{ $it->item_name ?: '' }}</td>
                            <td style="text-align:center;">{{ $it->unit ?: '' }}</td>
                            <td style="text-align:center;">{{ $it->quantity !== null ? rtrim(rtrim(number_format((float)$it->quantity, 2, '.', ''), '0'), '.') : '' }}</td>
                            <td style="text-align:center;">{{ $it->value !== null ? number_format((float)$it->value, 0, ',', '.') : '' }}</td>
                            <td>{{ $it->note ?: '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                @php
                    $totalQty = $borrowRequest->items->sum(function ($x) { return (float) ($x->quantity ?? 0); });
                    $totalValue = $borrowRequest->items->sum(function ($x) { return (float) ($x->value ?? 0); });
                    $totalQtyLabel = $totalQty ? rtrim(rtrim(number_format((float) $totalQty, 2, '.', ''), '0'), '.') : '';
                    $totalValueLabel = $totalValue ? (number_format((float) $totalValue, 0, ',', '.') . ' đ') : '';
                @endphp
                <tfoot>
                    <tr>
                        <td></td>
                        <td colspan="2" style="text-align:center;">Tổng cộng: {{ $totalValueLabel }}</td>
                        <td style="text-align:center;">{{ $totalQtyLabel }}</td>
                        <td style="text-align:center;"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="br-foot">
                @php $project = $borrowRequest->current_project ?: ''; @endphp
                <div class="br-terms">
                    <div class="sec">
                        <div class="ttl">2. Công nợ hiện tại của KH với công ty:</div>
                        @php
                            $hasDebt = trim((string) $project) !== '';
                            $tickYes = $hasDebt ? '☑' : '☐';
                            $tickNo = !$hasDebt ? '☑' : '☐';
                        @endphp
                        <ul>
                            <li>
                                Có {{ $tickYes }}
                                &nbsp;&nbsp;Không {{ $tickNo }}
                            </li>
                            <li>Số tiền: <span class="br-line wide">{{ $project }}</span></li>
                        </ul>
                    </div>

                    <div class="sec">
                        <div class="ttl">3. Đặt cọc:</div>
                        @php
                            $depText = (string) ($borrowRequest->deposit_text ?? '');
                            $hasDeposit = $depText === 'Có cọc';
                            $tickDepYes = $hasDeposit ? '☑' : '☐';
                            $tickDepNo = !$hasDeposit ? '☑' : '☐';
                            $depAmount = $borrowRequest->deposit_amount;
                            $depAmountLabel = $depAmount !== null && $depAmount !== '' ? number_format((float) $depAmount, 0, ',', '.') : '';
                        @endphp
                        <div style="margin-bottom: 4px;">
                            Có cọc {{ $tickDepYes }}
                            &nbsp;&nbsp;Không cọc {{ $tickDepNo }}
                        </div>
                        <div>Số tiền cọc: <span class="br-line short">{{ $depAmountLabel }}</span> (vnđ)</div>
                        <div style="font-style: italic; line-height: 1.35;">
                            (Lưu ý: số tiền cọc này sẽ hoàn trả sau khi khách hàng trả hàng đủ &amp; đúng theo thỏa thuận. Nếu phát hiện hàng bị hư hỏng, so với hiện trạng ban đầu, số tiền cọc sẽ được bên Vigilance giữ lại để cân trừ sau khi có biên bản xác nhận mức độ hỏng, thiệt hại giữa 2 bên).
                        </div>
                    </div>

                    <div class="sec">
                        <div class="ttl">4. Phạt quá hạn:</div>
                        <div>Trường hợp bên mượn quá hạn phải thanh toán cho bên Vigilance như sau</div>
                        <ul>
                            <li>Phí phạt: 1-3% giá trị hàng/ngày áp dụng với khách lẻ hoặc 0.5-1% giá trị hàng/ngày áp dụng với đại lý. Phí phạt này sẽ được cân trừ vào tiền cọc khi mượn hàng.</li>
                            <li>Hoặc chuyển sang bán hàng (quá 7 ngày kể từ ngày trễ hạn, Công ty có quyền chuyển đổi sản phẩm từ bán hàng và xuất hóa đơn tương ứng. Bên mượn phải hoàn toàn chịu trách nhiệm và đồng ý đã mượn).</li>
                        </ul>
                    </div>

                    <div class="br-disclaimer">
                        Phiếu đề nghị này chỉ áp dụng trong việc cho mượn hàng, không thay thế cho các đơn hàng/báo giá/hợp đồng mua bán giữa 2 bên.
                    </div>
                </div>

                <div class="br-sign">
                    <div class="side">
                        <div class="cap">Người đề nghị</div>
                        <div style="margin-top: 70px; font-weight: 700;">{{ $requestedByDisplay }}</div>
                    </div>

                    <div class="mid">
                        <div class="mid-title">Chấp nhận bởi</div>
                        <div class="mid-row">
                            <div class="mid-col">
                                <div class="cap">HCKT</div>
                                <div style="margin-top: 70px; font-weight: 700;"></div>
                            </div>
                            <div class="mid-col">
                                <div class="cap">Giám Đốc</div>
                                <div style="margin-top: 70px; font-weight: 700;">{{ $approvedByDisplay }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="side">
                        <div class="cap">Xuất kho bởi</div>
                        <div style="margin-top: 70px; font-weight: 700;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
