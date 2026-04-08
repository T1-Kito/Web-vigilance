@extends('layouts.admin')

@section('title', 'Quản lý công nợ')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Quản lý công nợ</h1>
            <div class="text-muted">Theo dõi các khoản phải thu từ đơn hàng báo giá.</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-8">
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Mã công nợ / số đơn hàng / khách hàng / MST">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        @foreach(['unpaid' => 'Chưa thanh toán', 'partial' => 'Thanh toán một phần', 'paid' => 'Đã thanh toán', 'overdue' => 'Quá hạn'] as $k => $lb)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $lb }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Lọc</button>
                    <a href="{{ route('admin.debts.index') }}" class="btn btn-light border">Xóa</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Mã công nợ</th>
                            <th>Số đơn hàng</th>
                            <th>Khách hàng</th>
                            <th>Tổng phải thu</th>
                            <th>Đã thu</th>
                            <th>Còn lại</th>
                            <th>Hạn thanh toán</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($debts as $debt)
                        @php
                            $badge = $debt->status === 'paid' ? 'success' : ($debt->status === 'partial' ? 'warning' : ($debt->status === 'overdue' ? 'danger' : 'secondary'));
                            $label = ['unpaid' => 'Chưa thanh toán', 'partial' => 'Thanh toán một phần', 'paid' => 'Đã thanh toán', 'overdue' => 'Quá hạn'][$debt->status] ?? $debt->status;
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $debt->debt_code }}</td>
                            <td>{{ $debt->salesOrder->sales_order_code ?? '---' }}</td>
                            <td>{{ optional($debt->salesOrder)->invoice_company_name ?: optional($debt->salesOrder)->receiver_name ?: '---' }}</td>
                            <td class="fw-semibold">{{ number_format((float) $debt->total_amount, 0, ',', '.') }}đ</td>
                            <td class="text-success fw-semibold">{{ number_format((float) $debt->paid_amount, 0, ',', '.') }}đ</td>
                            <td class="text-danger fw-semibold">{{ number_format((float) $debt->remaining_amount, 0, ',', '.') }}đ</td>
                            <td>{{ optional($debt->due_date)->format('d/m/Y') ?: '---' }}</td>
                            <td><span class="badge bg-{{ $badge }}">{{ $label }}</span></td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.debts.show', $debt) }}" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-4 text-muted">Chưa có dữ liệu công nợ.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $debts->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
