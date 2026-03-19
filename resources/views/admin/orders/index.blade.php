@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0" style="color: #2c3e50; font-weight: 700;">
                <i class="bi bi-cart-check me-2" style="color: #3498db;"></i> Quản lý đơn hàng
            </h1>
            <p class="text-muted mb-0" style="font-size: 1.1em;">Quản lý và theo dõi tất cả đơn hàng của khách hàng</p>
        </div>
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
                <tr style="border-bottom: 1px solid #f1f3f4; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
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
                    <td style="border: none; padding: 16px 12px;">
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; padding: 8px 12px; font-weight: 600; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);" title="Xem chi tiết">
                                <i class="bi bi-eye me-1"></i>Xem
                            </a>
                            <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border: none; border-radius: 8px; padding: 8px 12px; font-weight: 600; box-shadow: 0 4px 12px rgba(250, 112, 154, 0.3);" title="Xóa đơn hàng">
                                    <i class="bi bi-trash me-1"></i>Xóa
                                </button>
                            </form>
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
/* Custom hover effects */
.table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

/* Smooth transitions */
.table tbody tr {
    transition: all 0.3s ease;
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
@endsection 