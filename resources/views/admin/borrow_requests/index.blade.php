@extends('layouts.admin')

@section('title', 'Quản lý mượn hàng')

@section('content')
<div class="content-card">
    <div style="padding: 25px 30px; border-bottom: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; gap: 15px; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; padding: 12px 20px; border-radius: 12px; font-weight: 600; font-size: 1.1em;">
                    <i class="bi bi-clipboard-check me-2"></i>Tổng: {{ $requests->total() }} phiếu
                </div>
                @if(session('success'))
                    <div style="background: #dbeafe; color: #1e40af; padding: 10px 15px; border-radius: 8px; font-weight: 500;">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div style="background: #fee2e2; color: #991b1b; padding: 10px 15px; border-radius: 8px; font-weight: 500;">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    </div>
                @endif
            </div>
            <a href="{{ route('admin.borrow-requests.create') }}" style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 1.1em; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); transition: all 0.3s ease;">
                <i class="bi bi-plus-circle"></i>Tạo phiếu
            </a>
        </div>

        <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <form method="GET" action="{{ route('admin.borrow-requests.index') }}" style="display:flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo mã phiếu / khách hàng / mục đích..." style="padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1em; min-width: 260px;">
                <select name="status" style="padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1em; min-width: 220px;">
                    <option value="">Tất cả trạng thái</option>
                    @foreach($statusOptions as $k => $label)
                        <option value="{{ $k }}" {{ (string)request('status')===(string)$k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 1em;">
                    <i class="bi bi-search me-2"></i>Tìm
                </button>
                @if(request('q') || request('status'))
                    <a href="{{ route('admin.borrow-requests.index') }}" style="background: linear-gradient(135deg, #6b7280, #4b5563); color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 1em;">
                        <i class="bi bi-x-circle me-2"></i>Xóa lọc
                    </a>
                @endif
            </form>
        </div>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 1.05em;">
            <thead>
                <tr style="background: linear-gradient(135deg, #1e3a8a, #1e40af); color: white;">
                    <th style="padding: 16px 12px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">#</th>
                    <th style="padding: 16px 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #3b82f6;">Mã phiếu</th>
                    <th style="padding: 16px 12px; text-align: left; font-weight: 600; border-bottom: 2px solid #3b82f6;">Khách hàng</th>
                    <th style="padding: 16px 12px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">Thời gian</th>
                    <th style="padding: 16px 12px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">Trạng thái</th>
                    <th style="padding: 16px 12px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">Dòng</th>
                    <th style="padding: 16px 12px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $r)
                    @php
                        $st = $r->display_status;
                        $stLabel = $statusOptions[$st] ?? $st;
                        $badgeStyle = 'background: linear-gradient(135deg, #6b7280, #4b5563);';
                        if ($st === 'proposed') $badgeStyle = 'background: linear-gradient(135deg, #f59e0b, #d97706);';
                        if ($st === 'processing') $badgeStyle = 'background: linear-gradient(135deg, #3b82f6, #1d4ed8);';
                        if ($st === 'borrowing') $badgeStyle = 'background: linear-gradient(135deg, #8b5cf6, #6d28d9);';
                        if ($st === 'returned') $badgeStyle = 'background: linear-gradient(135deg, #10b981, #059669);';
                        if ($st === 'overdue') $badgeStyle = 'background: linear-gradient(135deg, #ef4444, #dc2626);';
                    @endphp
                    <tr style="border-bottom: 1px solid #e5e7eb; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='white'">
                        <td style="padding: 14px 12px; text-align: center; font-weight: 600; color: #6b7280;">{{ $loop->iteration }}</td>
                        <td style="padding: 14px 12px;">
                            <div style="font-weight: 700; color: #1f2937;">{{ $r->code ?: ('#' . $r->id) }}</div>
                            <div style="color:#6b7280; font-size:0.9em;">Tạo: {{ optional($r->created_at)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td style="padding: 14px 12px;">
                            <div style="font-weight: 700; color: #1f2937;">{{ $r->customer_name ?: '-' }}</div>
                            <div style="color:#6b7280; font-size:0.9em;">Người đề nghị: {{ $r->requested_by_name ?: (optional($r->requestedByAdmin)->name ?: 'Admin') }}</div>
                        </td>
                        <td style="padding: 14px 12px; text-align:center;">
                            <div style="font-weight: 600;">{{ $r->borrow_from ? $r->borrow_from->format('d/m/Y') : '-' }} → {{ $r->borrow_to ? $r->borrow_to->format('d/m/Y') : '-' }}</div>
                        </td>
                        <td style="padding: 14px 12px; text-align:center;">
                            <span class="badge" style="{{ $badgeStyle }} color:white; border-radius: 20px; padding: 8px 14px; font-weight: 700; font-size: 0.9em;">{{ $stLabel }}</span>
                        </td>
                        <td style="padding: 14px 12px; text-align:center; font-weight:700;">{{ (int) ($r->items_count ?? 0) }}</td>
                        <td style="padding: 14px 12px; text-align:center;">
                            <div class="dropdown">
                                <button
                                    class="btn btn-link text-white p-1 rounded-2"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    title="Thao tác"
                                    style="background: rgba(15,23,42,0.08); border-radius: 999px; width: 36px; height: 36px; display:inline-flex; align-items:center; justify-content:center;"
                                >
                                    <i class="bi bi-three-dots-vertical fs-5 lh-1"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.borrow-requests.show', $r) }}">
                                            <i class="bi bi-eye me-2 text-primary"></i>Xem
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.borrow-requests.edit', $r) }}">
                                            <i class="bi bi-pencil-square me-2 text-primary"></i>Sửa
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('admin.borrow-requests.destroy', $r) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phiếu này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bi bi-trash me-2"></i>Xóa
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 22px 12px; text-align:center; color:#6b7280;">Chưa có phiếu mượn hàng.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="padding: 18px 20px;">
        {{ $requests->links() }}
    </div>
</div>
@endsection
