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
        .br-title { text-align:center; font-weight: 600; margin: 22px 0 14px; letter-spacing: 0.5px; }
        .br-row { display:flex; gap: 16px; flex-wrap: wrap; margin-bottom: 10px; }
        .br-field { width: 100%; }
        .br-field .lbl { display:inline-block; min-width: 140px; }
        .br-line { display:inline-block; min-width: 193px; padding: 0 4px 0; }
        .br-line.wide { min-width: 420px; }
        .br-value { border-bottom: 1px solid #9ca3af; padding-bottom: 2px; }
        .br-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .br-table th, .br-table td { border: 1.2px solid #111827; padding: 10px 8px; font-size: 0.95rem; }
        .br-table th { text-align:center; font-weight: 600; background: #f3f4f6; }
        .br-table td { vertical-align: top; }
        .br-check { display:flex; align-items:center; gap: 8px; margin-top: 6px; }
        .br-box { width: 14px; height: 14px; border: 1.5px solid #111827; display:inline-block; }
        .br-box.checked { background: #111827; box-shadow: inset 0 0 0 2px #fff; }
        .br-sign { display:flex; justify-content:space-between; gap: 16px; margin-top: 30px; }
        .br-sign .col { width: 48%; text-align:center; }
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

            <div class="br-row">
                <div class="br-field">
                    <span class="lbl">Người đề nghị:</span>
                    <span class="br-line wide {{ $requestedByDisplay ? 'br-value' : '' }}">{{ $requestedByDisplay }}</span>
                </div>
            </div>

            <div class="br-row">
                <div class="br-field">
                    <span class="lbl">Khách hàng:</span>
                    @php $customerName = $borrowRequest->customer_name ?: ''; @endphp
                    <span class="br-line wide {{ $customerName ? 'br-value' : '' }}">{{ $customerName }}</span>
                </div>
            </div>

            <div class="br-row">
                <div class="br-field">
                    <span class="lbl">Mục đích:</span>
                    @php $purpose = $borrowRequest->purpose ?: ''; @endphp
                    <span class="br-line wide {{ $purpose ? 'br-value' : '' }}">{{ $purpose }}</span>
                </div>
            </div>

            <div class="br-row" style="margin-bottom: 4px;">
                <div class="br-field">
                    <span class="lbl">Thời gian mượn:</span>
                    
                    @php $borrowFrom = $borrowRequest->borrow_from ? $borrowRequest->borrow_from->format('d/m/Y') : ''; @endphp
                    <span class="br-line {{ $borrowFrom ? 'br-value' : '' }}">{{ $borrowFrom }}</span>
                    <span>đến</span>
                    @php $borrowTo = $borrowRequest->borrow_to ? $borrowRequest->borrow_to->format('d/m/Y') : ''; @endphp
                    <span class="br-line {{ $borrowTo ? 'br-value' : '' }}">{{ $borrowTo }}</span>
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
            </table>

            <div class="br-foot">
                <div class="br-row">
                    <div class="br-field">
                        <span class="lbl">Công nợ hiện tại:</span>
                        @php $project = $borrowRequest->current_project ?: ''; @endphp
                        <span class="br-line wide {{ $project ? 'br-value' : '' }}">{{ $project }}</span>
                    </div>
                </div>

                <div class="br-row">
                    <div class="br-field">
                        <span class="lbl">Đề xuất:</span>
                        <div class="br-check">
                            <span class="br-box {{ ($borrowRequest->deposit_text ?: 'Không cọc') === 'Không cọc' ? 'checked' : '' }}"></span>
                            <span>Không cọc</span>
                        </div>
                        <div class="br-check">
                            <span class="br-box {{ ($borrowRequest->deposit_text ?: '') === 'Có cọc' ? 'checked' : '' }}"></span>
                            <span>Cọc:</span>
                            @php
                                $depAmount = $borrowRequest->deposit_amount;
                                $depAmountLabel = $depAmount !== null && $depAmount !== '' ? number_format((float) $depAmount, 0, ',', '.') : '';
                            @endphp
                            <span class="br-line {{ ($borrowRequest->deposit_text ?: '') === 'Có cọc' ? 'br-value' : '' }}">{{ ($borrowRequest->deposit_text ?: '') === 'Có cọc' ? ($depAmountLabel !== '' ? ($depAmountLabel . ' đ') : '') : '' }}</span>
                        </div>
                    </div>
                </div>

                <div class="br-sign">
                    <div class="col">
                        <div class="cap">Người đề nghị</div>
                        <div style="margin-top: 70px; font-weight: 700;">{{ $requestedByDisplay }}</div>
                    </div>
                    <div class="col">
                        <div class="cap">Ký duyệt</div>
                        <div style="margin-top: 70px; font-weight: 700;">{{ $approvedByDisplay }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
