@extends('layouts.admin')

@section('title', request()->query('type') === 'quote' ? 'Chi tiết báo giá' : 'Chi tiết đơn hàng')

@section('content')
 @php
     $isQuote = request()->query('type') === 'quote';
     $hasDeliveries = \Illuminate\Support\Facades\Schema::hasTable('deliveries')
         ? \App\Models\Delivery::query()->where('order_id', $order->id)->exists()
         : false;
     $orderCode = $order->order_code ?? ("VK" . str_pad($order->id, 6, '0', STR_PAD_LEFT));
     $quoteUrl = route('orders.quote', ['orderCode' => $order->order_code]);
     $quoteEmbedUrl = $quoteUrl . '?embed=1';

     $items = $order->items ?? collect();
     $total = (int) $items->sum(function($i){ return (int) ($i->price ?? 0) * (int) ($i->quantity ?? 0); });

     $statusKey = (string) ($order->status ?? '');
     $statusLabel = match($statusKey) {
         'pending' => 'Chờ xử lý',
         'processing' => 'Đang xử lý',
         'completed' => 'Hoàn thành',
         'cancelled' => 'Đã hủy',
         default => $statusKey,
     };
     $statusBadge = match($statusKey) {
         'pending', 'processing' => 'warning',
         'completed' => 'success',
         'cancelled' => 'danger',
         default => 'secondary',
     };

     $mailTo = null;
     if (!empty($order->customer_email)) {
         $subject = rawurlencode(($isQuote ? 'Báo giá ' : 'Đơn hàng ') . $orderCode);
         $body = rawurlencode("Xin chào,\n\n" . ($isQuote ? 'Thông tin báo giá: ' : 'Thông tin đơn hàng: ') . $orderCode . "\nTrạng thái: " . $statusLabel . "\nTổng tiền: " . number_format($total, 0, ',', '.') . "đ\n\nTrân trọng,");
         $mailTo = 'mailto:' . $order->customer_email . '?subject=' . $subject . '&body=' . $body;
     }

     $timelineSteps = [
         'pending' => $isQuote ? 'Báo giá' : 'Đặt hàng',
         'processing' => 'Đang xử lý',
         'completed' => 'Hoàn thành',
         'cancelled' => 'Đã hủy',
     ];
     $activeStep = array_key_exists($statusKey, $timelineSteps) ? $statusKey : 'pending';

     $backUrl = route('admin.orders.index');
     if ($isQuote) {
         $backUrl .= '?type=quote';
     }
 @endphp

 <div class="content-card p-4">
     <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
         <div>
             <h2 class="fw-bold mb-1" style="color:#0f172a;">{{ $isQuote ? 'Chi tiết báo giá' : 'Chi tiết đơn hàng' }}</h2>
             <div class="text-muted">{{ $isQuote ? 'Mã báo giá' : 'Mã đơn' }}: <span class="fw-semibold">{{ $orderCode }}</span></div>
         </div>
         <div class="d-flex align-items-center gap-2">
             <span class="badge bg-{{ $statusBadge }}" style="padding:.6rem .9rem;border-radius:999px;">{{ $statusLabel }}</span>
             <a href="{{ $backUrl }}" class="btn btn-outline-secondary">Quay lại</a>
         </div>
     </div>

     <div class="row g-4">
         <div class="col-lg-8">
            <div class="card shadow-sm" style="border:none;border-radius:16px;">
                 <div class="card-body">
                    <div class="fw-bold mb-3" style="font-size:1.05rem;color:#0f172a;">{{ $isQuote ? 'Thông tin báo giá' : 'Thông tin đơn hàng' }}</div>
                     <div class="row g-3">
                         <div class="col-md-6">
                             <div class="text-muted" style="font-size:.85rem;">Ngày đặt</div>
                             <div class="fw-semibold">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                         </div>
                     </div>
                 </div>
             </div>

             <div class="card shadow-sm mt-4" style="border:none;border-radius:16px;">
                <div class="card-body">
                    @php
                        $hasInvoiceInfo = !empty($order->customer_email)
                            || !empty($order->customer_tax_code)
                            || !empty($order->invoice_company_name)
                            || !empty($order->invoice_address);
                    @endphp

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fw-bold" style="font-size:1.05rem;color:#0f172a;">Thông tin khách hàng</div>
                        <span class="badge text-bg-light" style="border-radius:999px;">Nhận hàng</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted" style="font-size:.85rem;">Người nhận</div>
                            <div class="fw-semibold">{{ $order->receiver_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted" style="font-size:.85rem;">SĐT</div>
                            <div class="fw-semibold">{{ $order->receiver_phone }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted" style="font-size:.85rem;">Địa chỉ giao hàng</div>
                            <div class="fw-semibold">{{ $order->receiver_address }}</div>
                        </div>
                        @if($order->note)
                            <div class="col-12">
                                <div class="text-muted" style="font-size:.85rem;">Ghi chú</div>
                                <div class="fw-semibold">{{ $order->note }}</div>
                            </div>
                        @endif
                    </div>

                    @if($hasInvoiceInfo)
                        <hr class="my-3">

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="fw-bold" style="font-size:1.0rem;color:#0f172a;">Thông tin xuất hoá đơn</div>
                            <span class="badge text-bg-light" style="border-radius:999px;">Tuỳ chọn</span>
                        </div>

                        <div class="row g-3">
                            @if(!empty($order->customer_email))
                                <div class="col-md-6">
                                    <div class="text-muted" style="font-size:.85rem;">Email nhận duyệt đơn</div>
                                    <div class="fw-semibold">{{ $order->customer_email }}</div>
                                </div>
                            @endif
                            @if(!empty($order->customer_tax_code))
                                <div class="col-md-6">
                                    <div class="text-muted" style="font-size:.85rem;">Mã số thuế</div>
                                    <div class="fw-semibold">{{ $order->customer_tax_code }}</div>
                                </div>
                            @endif
                            @if(!empty($order->invoice_company_name))
                                <div class="col-12">
                                    <div class="text-muted" style="font-size:.85rem;">Tên công ty</div>
                                    <div class="fw-semibold">{{ $order->invoice_company_name }}</div>
                                </div>
                            @endif
                            @if(!empty($order->invoice_address))
                                <div class="col-12">
                                    <div class="text-muted" style="font-size:.85rem;">Địa chỉ công ty</div>
                                    <div class="fw-semibold">{{ $order->invoice_address }}</div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
         </div>

         <div class="col-lg-4">
             <div class="card shadow-sm" style="border:none;border-radius:16px; position: sticky; top: 16px;">
                 <div class="card-body">
                     <div class="d-flex align-items-center justify-content-between mb-3">
                         <div class="fw-bold" style="font-size:1.05rem;color:#0f172a;">Trạng thái</div>
                         <span class="badge bg-{{ $statusBadge }}" style="width:24px;height:24px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;">{{ $statusLabel }}</span>
                     </div>

                     <form action="{{ route('admin.orders.update', $order) }}" method="POST">
                         @csrf
                         @method('PATCH')
                         <div class="row g-2">
                             <div class="col-12">
                                 <select name="status" class="form-select" {{ $hasDeliveries ? 'disabled' : '' }}>
                                     <option value="pending" @if($order->status=='pending') selected @endif>Chờ xử lý</option>
                                     <option value="processing" @if($order->status=='processing') selected @endif>Đang xử lý</option>
                                     <option value="completed" @if($order->status=='completed') selected @endif>Hoàn thành</option>
                                     <option value="cancelled" @if($order->status=='cancelled') selected @endif>Đã hủy</option>
                                 </select>
                             </div>
                             <div class="col-12 d-grid">
                                 <button type="submit" class="btn btn-success" {{ $hasDeliveries ? 'disabled' : '' }}>Cập nhật</button>
                             </div>
                         </div>
                     </form>

                     @if($hasDeliveries)
                         <div class="alert alert-warning mt-3 mb-0 small">
                             Đơn hàng đã phát sinh phiếu xuất kho. Trạng thái được quản lý theo chứng từ kho để đảm bảo tính pháp lý.
                         </div>
                     @endif

                     <hr>

                     <div class="d-flex justify-content-between align-items-center">
                         <div class="text-muted">Tổng tiền</div>
                         <div class="fw-bold" style="font-size:1.25rem; color:#ef4444;">{{ number_format($total, 0, ',', '.') }}đ</div>
                     </div>

                     <div class="row g-2 mt-3">
                         <div class="col-12">
                             <div class="text-muted" style="font-size:.85rem;">Thao tác nhanh</div>
                         </div>

                         <div class="col-12 d-grid">
                             <a class="btn btn-outline-primary" href="{{ $quoteUrl }}" target="_blank" rel="noopener">Mở báo giá</a>
                         </div>

                         <div class="col-12 d-grid">
                             <a class="btn btn-primary" href="{{ route('admin.orders.workflow', $order) }}">Mở module chứng từ (Xuất kho/Hóa đơn)</a>
                         </div>

                         <div class="col-12 d-grid">
                             @if($mailTo)
                                 <a class="btn btn-outline-success" href="{{ $mailTo }}">Gửi email</a>
                             @else
                                 <button class="btn btn-outline-success" type="button" disabled>Gửi email</button>
                             @endif
                         </div>
                     </div>

                     <hr>

                     <div class="fw-bold mb-2" style="font-size:1.05rem;color:#0f172a;">Timeline</div>
                     <div class="d-flex flex-column gap-2">
                         @foreach($timelineSteps as $key => $label)
                             @php
                                 $isActive = $key === $activeStep;
                                 $isDone = ($activeStep === 'completed' && $key !== 'cancelled') || ($activeStep === 'processing' && in_array($key, ['pending','processing'], true)) || ($activeStep === 'pending' && $key === 'pending');
                                 if ($activeStep === 'cancelled') {
                                     $isDone = $key === 'cancelled';
                                 }
                             @endphp
                             <div class="d-flex align-items-center gap-2">
                                 <span class="badge {{ $isActive ? 'bg-primary' : ($isDone ? 'bg-success' : 'bg-light text-secondary') }}" style="width:24px;height:24px;border-radius:999px;display:inline-flex;align-items:center;justify-content:center;">{{ $loop->iteration }}</span>
                                 <div class="fw-semibold" style="color: {{ $isActive ? '#0f172a' : '#64748b' }};">{{ $label }}</div>
                             </div>
                         @endforeach
                     </div>

                     <hr>

                     <div class="d-grid gap-2">
                         <form action="{{ route('admin.orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này?')">
                             @csrf
                             @method('DELETE')
                             <button type="submit" class="btn btn-outline-danger fw-bold">Xóa đơn hàng</button>
                         </form>
                     </div>
                 </div>
             </div>
         </div>
     </div>
 </div>
@endsection