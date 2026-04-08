@extends('layouts.admin')

@section('title', 'Chi tiết công nợ')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Công nợ: {{ $debt->debt_code }}</h1>
            <div class="text-muted">Đơn hàng: {{ $debt->salesOrder->sales_order_code ?? '---' }}</div>
        </div>
        <a href="{{ route('admin.debts.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Thông tin công nợ</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="text-muted small">Mã công nợ</div><div class="fw-semibold">{{ $debt->debt_code }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Số đơn hàng</div><div class="fw-semibold">{{ $debt->salesOrder->sales_order_code ?? '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Khách hàng</div><div class="fw-semibold">{{ optional($debt->salesOrder)->invoice_company_name ?: optional($debt->salesOrder)->receiver_name ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">MST</div><div class="fw-semibold">{{ optional($debt->salesOrder)->customer_tax_code ?: '---' }}</div></div>
                        <div class="col-md-4"><div class="text-muted small">Tổng phải thu</div><div class="fw-bold text-danger">{{ number_format((float) $debt->total_amount, 0, ',', '.') }}đ</div></div>
                        <div class="col-md-4"><div class="text-muted small">Đã thu</div><div class="fw-bold text-success">{{ number_format((float) $debt->paid_amount, 0, ',', '.') }}đ</div></div>
                        <div class="col-md-4"><div class="text-muted small">Còn lại</div><div class="fw-bold text-warning">{{ number_format((float) $debt->remaining_amount, 0, ',', '.') }}đ</div></div>
                        <div class="col-md-6"><div class="text-muted small">Hạn thanh toán</div><div class="fw-semibold">{{ optional($debt->due_date)->format('d/m/Y') ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Ngày thu gần nhất</div><div class="fw-semibold">{{ optional($debt->last_paid_at)->format('d/m/Y') ?: '---' }}</div></div>
                        <div class="col-12"><div class="text-muted small">Ghi chú</div><div class="fw-semibold">{{ $debt->note ?: '---' }}</div></div>
                    </div>
                </div>
            </div>

            @if($debt->salesOrder)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Chứng từ liên quan</div>
                    <div class="card-body d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.sales-orders.show', $debt->salesOrder) }}" class="btn btn-outline-primary btn-sm">Xem đơn hàng</a>
                        @if($debt->salesOrder->quote)
                            <a href="{{ route('admin.quotes.show', $debt->salesOrder->quote) }}" class="btn btn-outline-secondary btn-sm">Xem báo giá</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 16px;">
                <div class="card-header bg-white fw-bold">Cập nhật công nợ</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.debts.update', $debt) }}" class="row g-3">
                        @csrf
                        @method('PATCH')
                        <div class="col-12">
                            <label class="form-label">Số tiền thu kỳ này</label>
                            <input type="number" name="collected_amount" min="0" step="1000" class="form-control" value="{{ old('collected_amount', 0) }}" required>
                            <div class="form-text">Hệ thống tự cộng vào số đã thu hiện tại.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ngày thu tiền</label>
                            <input type="date" name="collected_at" class="form-control" value="{{ old('collected_at', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Hạn thanh toán</label>
                            <input type="date" name="due_date" class="form-control" value="{{ old('due_date', optional($debt->due_date)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Diễn giải thu tiền</label>
                            <textarea name="note" rows="3" class="form-control" placeholder="VD: Thu chuyển khoản đợt 1">{{ old('note', $debt->note) }}</textarea>
                        </div>
                        <div class="col-12 d-grid">
                            <button type="submit" class="btn btn-primary">Ghi nhận thu tiền</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
