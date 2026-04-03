@extends('layouts.admin')

@section('title', 'Quản lý mượn hàng')

@section('content')
<style>
    .br-page {
        --br-bg: #f3f6fb;
        --br-card: #ffffff;
        --br-border: #e6ebf2;
        --br-text: #0f172a;
        --br-muted: #64748b;
        --br-primary: #2563eb;
        --br-primary-soft: #eaf1ff;
        --br-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        --br-radius: 14px;
    }

    .br-page .content-card {
        background: var(--br-bg);
        border: 1px solid var(--br-border);
        border-radius: 16px;
        box-shadow: none;
        overflow: hidden;
    }

    .br-header {
        padding: 20px 22px;
        border-bottom: 1px solid var(--br-border);
        background: #fff;
    }

    .br-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .br-title-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .br-summary {
        background: var(--br-primary-soft);
        color: var(--br-primary);
        border: 1px solid #dbe7ff;
        padding: 9px 14px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
    }

    .br-btn-create {
        background: var(--br-primary);
        color: #fff;
        border: 1px solid var(--br-primary);
        padding: 10px 16px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all .2s ease;
    }

    .br-btn-create:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
        transform: translateY(-1px);
    }

    .br-alert {
        border-radius: 10px;
        padding: 9px 12px;
        font-weight: 500;
        font-size: 13px;
    }

    .br-alert.success {
        background: #ecfdf3;
        color: #027a48;
        border: 1px solid #c7f4dc;
    }

    .br-alert.error {
        background: #fff1f2;
        color: #be123c;
        border: 1px solid #ffd6dc;
    }

    .br-filter {
        background: #fff;
        border: 1px solid var(--br-border);
        border-radius: 12px;
        padding: 14px;
    }

    .br-form {
        display: grid;
        grid-template-columns: 1.7fr 1fr auto auto;
        gap: 10px;
        align-items: center;
    }

    .br-input,
    .br-select {
        width: 100%;
        border: 1px solid #d7deea;
        background: #fff;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 14px;
        color: var(--br-text);
        outline: none;
    }

    .br-input:focus,
    .br-select:focus {
        border-color: #adc5ff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .br-btn {
        border-radius: 10px;
        border: 1px solid transparent;
        padding: 9px 14px;
        font-weight: 600;
        font-size: 14px;
        white-space: nowrap;
    }

    .br-btn.search {
        background: #1e293b;
        color: #fff;
    }

    .br-btn.clear {
        background: #fff;
        color: #334155;
        border-color: #cbd5e1;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .br-table-wrap {
        margin: 14px 14px 0;
        border: 1px solid var(--br-border);
        border-radius: var(--br-radius);
        overflow: hidden;
        background: #fff;
    }

    .br-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 14px;
    }

    .br-table thead tr {
        background: #f8fafc;
    }

    .br-table th {
        padding: 13px 12px;
        color: #334155;
        font-weight: 700;
        border-bottom: 1px solid var(--br-border);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .02em;
    }

    .br-table td {
        padding: 13px 12px;
        border-bottom: 1px solid #eef2f7;
        vertical-align: middle;
    }

    .br-table tbody tr:hover {
        background: #fafcff;
    }

    .br-code {
        font-weight: 700;
        color: var(--br-text);
    }

    .br-sub {
        color: var(--br-muted);
        font-size: 12px;
        margin-top: 2px;
    }

    .br-badge {
        border-radius: 999px;
        padding: 6px 12px;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
        min-width: 95px;
        text-align: center;
    }

    .br-pagination {
        padding: 14px 16px 18px;
    }

    @media (max-width: 1024px) {
        .br-form {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {
        .br-form {
            grid-template-columns: 1fr;
        }

        .br-header {
            padding: 16px;
        }

        .br-table-wrap {
            margin: 10px 10px 0;
        }
    }
</style>

<div class="br-page">
    <div class="content-card">
        <div class="br-header">
            <div class="br-row">
                <div class="br-title-wrap">
                    <div class="br-summary">
                        <i class="bi bi-clipboard-check me-1"></i>
                        Tổng: {{ $requests->total() }} phiếu
                    </div>

                    @if(session('success'))
                        <div class="br-alert success">
                            <i class="bi bi-check-circle me-1"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="br-alert error">
                            <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                        </div>
                    @endif
                </div>

                <a href="{{ route('admin.borrow-requests.create') }}" class="br-btn-create">
                    <i class="bi bi-plus-circle"></i>Tạo phiếu
                </a>
            </div>

            <div class="br-filter">
                <form method="GET" action="{{ route('admin.borrow-requests.index') }}" class="br-form">
                    <input class="br-input" type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo mã phiếu / khách hàng / mục đích...">

                    <select class="br-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        @foreach($statusOptions as $k => $label)
                            <option value="{{ $k }}" {{ (string)request('status')===(string)$k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="br-btn search">
                        <i class="bi bi-search me-1"></i>Tìm
                    </button>

                    @if(request('q') || request('status'))
                        <a href="{{ route('admin.borrow-requests.index') }}" class="br-btn clear">
                            <i class="bi bi-x-circle me-1"></i>Xóa lọc
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="br-table-wrap" style="overflow-x:auto;">
            <table class="br-table">
                <thead>
                    <tr>
                        <th style="text-align:center; width: 60px;">#</th>
                        <th style="text-align:left;">Mã phiếu</th>
                        <th style="text-align:left;">Khách hàng</th>
                        <th style="text-align:center;">Thời gian</th>
                        <th style="text-align:center;">Trạng thái</th>
                        <th style="text-align:center; min-width: 4.5rem;">Dòng</th>
                        <th style="text-align:right; min-width: 9rem; padding-right: 16px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $r)
                        @php
                            $st = $r->display_status;
                            $stLabel = $statusOptions[$st] ?? $st;
                            $badgeStyle = 'background:#64748b;';
                            if ($st === 'proposed') $badgeStyle = 'background:#f59e0b;';
                            if ($st === 'processing') $badgeStyle = 'background:#3b82f6;';
                            if ($st === 'borrowing') $badgeStyle = 'background:#8b5cf6;';
                            if ($st === 'returned') $badgeStyle = 'background:#10b981;';
                            if ($st === 'overdue') $badgeStyle = 'background:#ef4444;';
                        @endphp
                        <tr>
                            <td style="text-align:center; color:#64748b; font-weight:600;">{{ $loop->iteration }}</td>
                            <td>
                                <div class="br-code">{{ $r->code ?: ('#' . $r->id) }}</div>
                                <div class="br-sub">Tạo: {{ optional($r->created_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="br-code">{{ $r->customer_name ?: '-' }}</div>
                                <div class="br-sub">Người đề nghị: {{ $r->requested_by_name ?: (optional($r->requestedByAdmin)->name ?: 'Admin') }}</div>
                            </td>
                            <td style="text-align:center; font-weight:600; color:#1e293b;">
                                {{ $r->borrow_from ? $r->borrow_from->format('d/m/Y') : '-' }} → {{ $r->borrow_to ? $r->borrow_to->format('d/m/Y') : '-' }}
                            </td>
                            <td style="text-align:center;">
                                <span class="br-badge" style="{{ $badgeStyle }}">{{ $stLabel }}</span>
                            </td>
                            <td style="text-align:center; font-weight:700; color:#0f172a;">{{ (int) ($r->items_count ?? 0) }}</td>
                            <td style="text-align: right; vertical-align: middle; position: relative; min-width: 9rem;">
                                <div class="dropdown" style="display: inline-block; text-align: left;">
                                    <button
                                        class="btn btn-link text-dark p-1 rounded-2 borrow-request-actions-dropdown"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        title="Thao tác"
                                        style="background:#f1f5f9; border-radius: 999px; width: 34px; height: 34px; display:inline-flex; align-items:center; justify-content:center; text-decoration:none;"
                                    >
                                        <i class="bi bi-three-dots-vertical fs-5 lh-1"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small" style="z-index: 1080; min-width: 140px;">
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

        <div class="br-pagination">
            {{ $requests->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.borrow-request-actions-dropdown').forEach(function (toggleEl) {
        bootstrap.Dropdown.getOrCreateInstance(toggleEl, {
            popperConfig: function (defaultBsPopperConfig) {
                var mods = (defaultBsPopperConfig.modifiers || []).map(function (m) {
                    if (m.name === 'flip') {
                        return Object.assign({}, m, { enabled: false });
                    }
                    return m;
                });
                return Object.assign({}, defaultBsPopperConfig, {
                    strategy: 'fixed',
                    modifiers: mods,
                });
            },
        });
    });
});
</script>
@endsection
