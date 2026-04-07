@extends('layouts.admin')

@section('title', 'Đơn bán ngoài')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Danh sách đơn bán ngoài</h1>
            <div class="text-muted">Đơn bán được chốt từ báo giá (không bao gồm đơn web).</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-2" method="GET" action="{{ route('admin.sales-orders.index') }}">
                <div class="col-md-5">
                    <input class="form-control" type="text" name="q" value="{{ request('q') }}" placeholder="Mã đơn / khách / MST / SĐT">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái</option>
                        @foreach(['pending' => 'Chờ xử lý', 'processing' => 'Đang xử lý', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'] as $k => $lb)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $lb }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Lọc</button>
                    <a class="btn btn-light border" href="{{ route('admin.sales-orders.index') }}">Xóa lọc</a>
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
                            <th>Mã đơn bán</th>
                            <th>Khách hàng</th>
                            <th>Ngày tạo</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($salesOrders as $so)
                        <tr>
                            <td class="fw-semibold">{{ $so->sales_order_code }}</td>
                            <td>{{ $so->invoice_company_name ?: $so->receiver_name }}</td>
                            <td>{{ optional($so->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @php
                                    $badge = $so->status === 'completed' ? 'success' : ($so->status === 'cancelled' ? 'danger' : ($so->status === 'processing' ? 'warning' : 'secondary'));
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ $so->status }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.sales-orders.show', $so) }}" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có đơn bán ngoài.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $salesOrders->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
