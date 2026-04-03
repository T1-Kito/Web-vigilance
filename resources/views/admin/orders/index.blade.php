@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="h3 mb-0" style="color: #2c3e50; font-weight: 700;">
                <i class="bi bi-cart-check me-2" style="color: #3498db;"></i> Quản lý đơn hàng
            </h1>
            <p class="text-muted mb-0" style="font-size: 1.1em;">Quản lý và theo dõi tất cả đơn hàng của khách hàng</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.orders.create') }}" class="btn ao-create-order-btn rounded-pill px-4 fw-semibold" style="white-space: nowrap;">
                <span class="ao-create-order-btn__icon me-2 d-inline-flex align-items-center justify-content-center">
                    <i class="bi bi-plus-lg"></i>
                </span>
                Tạo đơn hàng
            </a>
        <div class="d-flex gap-3">
            <div class="text-end">
                <div class="h4 mb-0 fw-bold" style="color: #3498db;">{{ $orders->total() }}</div>
                <small class="text-muted">Tổng đơn hàng</small>
            </div>
                         <div class="text-end">
                 <div class="h4 mb-0 fw-bold" style="color: #e74c3c;">{{ \App\Models\Order::where('status', 'pending')->count() }}</div>
                 <small class="text-muted">Chờ xử lý</small>
             </div>
        </div>
        </div>
    </div>
    
    <style>
        .ao-create-order-btn {
            border: none !important;
            background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%) !important;
            color: #fff !important;
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.25);
            transition: transform 0.15s ease, box-shadow 0.15s ease, filter 0.15s ease;
        }
        .ao-create-order-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 30px rgba(79, 70, 229, 0.28);
            filter: brightness(1.02);
        }
        .ao-create-order-btn__icon {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.22);
        }
        .ao-create-order-btn__icon i {
            font-size: 1rem;
            line-height: 1;
        }
    </style>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; box-shadow: 0 4px 15px rgba(67, 233, 123, 0.3);">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; border: none; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; box-shadow: 0 4px 15px rgba(250, 112, 154, 0.3);">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <!-- Orders Table -->
    <div class="card shadow" style="border: none; border-radius: 16px; overflow: hidden;">
        <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
            <h6 class="m-0 font-weight-bold" style="font-size: 1.2em;">
                <i class="bi bi-list-ul me-2"></i>Danh sách đơn hàng
            </h6>
        </div>
        <div class="card-body p-0">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-2 mb-3 mx-0 px-3 pt-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Từ khoá</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Mã đơn / Tên / MST / SĐT...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Tên khách hàng</label>
                    <input type="text" name="customer_name" class="form-control" value="{{ request('customer_name') }}" placeholder="Nhập tên khách hàng">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Mã số thuế</label>
                    <input type="text" name="tax_code" class="form-control" value="{{ request('tax_code') }}" placeholder="MST">
                </div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label mb-1 fw-semibold text-secondary small">Trạng thái</label>
                    <select name="status" class="form-select">
                        @foreach($statusOptions as $k => $label)
                            <option value="{{ $k }}" {{ (string) request('status') === (string) $k ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-12 d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary flex-grow-1" type="submit" style="white-space:nowrap;"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    @if(request('q') || request('customer_name') || request('tax_code') || request('status'))
                        <a class="btn btn-outline-secondary flex-grow-1" href="{{ route('admin.orders.index') }}" style="white-space:nowrap;"><i class="bi bi-x-circle me-1"></i>Xóa</a>
                    @endif
                </div>
            </form>
            <div class="table-responsive">
                <table class="table mb-0" style="border: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Mã đơn</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Khách hàng</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Ngày đặt</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Trạng thái</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Tổng tiền</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Thao tác</th>
                        </tr>
                    </thead>
            <tbody>
                @foreach($orders as $order)
                <tr class="ao-row" data-href="{{ route('admin.orders.show', $order) }}" style="border-bottom: 1px solid #eef2f7;" onmouseover="this.style.backgroundColor='#eaf2ff'" onmouseout="this.style.backgroundColor='white'">
                    <td style="border: none; padding: 16px 12px;">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-weight: 600; font-size: 0.9em;">
                                <i class="bi bi-receipt"></i>
                            </div>
                            <div>
                                <strong style="color: #2c3e50; font-size: 1.1em;">{{ $order->order_code ?? ("VK" . str_pad($order->id, 6, '0', STR_PAD_LEFT)) }}</strong>
                                <br><small class="text-muted" style="font-size: 0.85em;">ID: {{ $order->id }}</small>
                            </div>
                        </div>
                    </td>
                    <td style="border: none; padding: 16px 12px;">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; font-weight: 600; font-size: 0.9em;">
                                {{ strtoupper(substr($order->receiver_name, 0, 2)) }}
                            </div>
                            <div>
                                <strong style="color: #2c3e50; font-size: 1.1em;">{{ $order->receiver_name }}</strong>
                                @if($order->receiver_phone)
                                    <br><small class="text-muted" style="font-size: 0.85em;">{{ $order->receiver_phone }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="border: none; padding: 16px 12px;">
                        <div class="d-flex flex-column">
                            <span style="font-weight: 600; color: #495057;">{{ $order->created_at->format('d/m/Y') }}</span>
                            <small class="text-muted" style="font-size: 0.85em;">{{ $order->created_at->format('H:i') }}</small>
                        </div>
                    </td>
                    <td style="border: none; padding: 16px 12px;">
                        @if($order->status == 'pending' || $order->status == 'processing')
                            <span class="badge" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                <i class="bi bi-clock me-1"></i>Chờ xử lý
                            </span>
                        @elseif($order->status == 'cancelled')
                            <span class="badge" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                <i class="bi bi-x-circle me-1"></i>Đã hủy
                            </span>
                        @else
                            <span class="badge" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                <i class="bi bi-check-circle me-1"></i>Hoàn thành
                            </span>
                        @endif
                    </td>
                    <td style="border: none; padding: 16px 12px;">
                        @php $total = $order->items->sum(function($i){ return $i->price * $i->quantity; }); @endphp
                        <div class="d-flex flex-column">
                            <span class="fw-bold" style="color: #e74c3c; font-size: 1.2em;">{{ number_format($total, 0, ',', '.') }}đ</span>
                            <small class="text-muted" style="font-size: 0.85em;">{{ $order->items->count() }} sản phẩm</small>
                        </div>
                    </td>
                    <td style="border: none; padding: 16px 12px; text-align:center;">
                        <div class="dropdown">
                            <button
                                class="btn btn-link text-secondary p-1 rounded-2"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                title="Thao tác"
                                style="background: rgba(15,23,42,0.05); border-radius: 999px; width: 36px; height: 36px; display:inline-flex; align-items:center; justify-content:center;"
                            >
                                <i class="bi bi-three-dots-vertical fs-5 lh-1"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.orders.show', $order) }}">
                                        <i class="bi bi-eye me-2 text-primary"></i>Xem
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
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
                @endforeach
            </tbody>
                 </table>
     </div>
 </div>
 
 <!-- Pagination -->
 <div class="d-flex justify-content-center mt-4">
     {{ $orders->links('pagination::bootstrap-5') }}
 </div>
    
    <!-- Empty State -->
    @if($orders->count() == 0)
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="bi bi-cart-x" style="font-size: 4em; color: #bdc3c7;"></i>
        </div>
        <h4 class="text-muted mb-2">Chưa có đơn hàng nào</h4>
        <p class="text-muted">Khi có đơn hàng mới, chúng sẽ xuất hiện ở đây.</p>
    </div>
    @endif
</div>

<style>
/* Đồng bộ hover xanh nhẹ như các bảng khác */
.ao-row {
    transition: background-color .15s ease;
    cursor: pointer;
}

.ao-row:hover {
    background: #eaf2ff;
}

.ao-row:hover td {
    background: #eaf2ff;
}

/* Custom scrollbar */
.table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ao-row').forEach(function (row) {
        row.addEventListener('click', function (e) {
            const blocked = e.target.closest('a,button,form,input,select,textarea,label,.dropdown-menu,.dropdown-toggle');
            if (blocked) return;

            const href = row.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });
});
</script>
@endsection 