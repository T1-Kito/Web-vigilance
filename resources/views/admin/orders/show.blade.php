@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng')

@section('content')
 @php
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
         $subject = rawurlencode('Đơn hàng ' . $orderCode);
         $body = rawurlencode("Xin chào,\n\nThông tin đơn hàng: " . $orderCode . "\nTrạng thái: " . $statusLabel . "\nTổng tiền: " . number_format($total, 0, ',', '.') . "đ\n\nTrân trọng,");
         $mailTo = 'mailto:' . $order->customer_email . '?subject=' . $subject . '&body=' . $body;
     }

     $timelineSteps = [
         'pending' => 'Đặt hàng',
         'processing' => 'Đang xử lý',
         'completed' => 'Hoàn thành',
         'cancelled' => 'Đã hủy',
     ];
     $activeStep = array_key_exists($statusKey, $timelineSteps) ? $statusKey : 'pending';
 @endphp

 <div class="content-card p-4">
     <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
         <div>
             <h2 class="fw-bold mb-1" style="color:#0f172a;">Chi tiết đơn hàng</h2>
             <div class="text-muted">Mã đơn: <span class="fw-semibold">{{ $orderCode }}</span></div>
         </div>
         <div class="d-flex align-items-center gap-2">
             <span class="badge bg-{{ $statusBadge }}" style="padding:.6rem .9rem;border-radius:999px;">{{ $statusLabel }}</span>
             <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">Quay lại</a>
         </div>
     </div>

     <div class="row g-4">
         <div class="col-lg-8">
             <div class="card shadow-sm" style="border:none;border-radius:16px;">
                 <div class="card-body">
                     <div class="fw-bold mb-3" style="font-size:1.05rem;color:#0f172a;">Thông tin đơn hàng</div>
                     <div class="row g-3">
                         <div class="col-md-6">
                             <div class="text-muted" style="font-size:.85rem;">Ngày đặt</div>
                             <div class="fw-semibold">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                         </div>
                         <div class="col-md-6">
                             <div class="text-muted" style="font-size:.85rem;">Phương thức thanh toán</div>
                             <div class="fw-semibold">{{ strtoupper($order->payment_method) }}</div>
                         </div>
                     </div>
                 </div>
             </div>

             <div class="card shadow-sm mt-4" style="border:none;border-radius:16px;">
                 <div class="card-body">
                     <div class="fw-bold mb-3" style="font-size:1.05rem;color:#0f172a;">Thông tin khách hàng</div>
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
                             <div class="text-muted" style="font-size:.85rem;">Địa chỉ</div>
                             <div class="fw-semibold">{{ $order->receiver_address }}</div>
                         </div>
                         @if($order->note)
                             <div class="col-12">
                                 <div class="text-muted" style="font-size:.85rem;">Ghi chú</div>
                                 <div class="fw-semibold">{{ $order->note }}</div>
                             </div>
                         @endif
                     </div>
                 </div>
             </div>

             <div class="card shadow-sm mt-4" style="border:none;border-radius:16px;">
                 <div class="card-body">
                     <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                         <div class="fw-bold" style="font-size:1.05rem;color:#0f172a;">Preview báo giá</div>
                         <div class="d-flex gap-2">
                             <a class="btn btn-outline-primary btn-sm fw-bold" href="{{ $quoteUrl }}" target="_blank" rel="noopener">Mở full</a>
                         </div>
                     </div>

                     <div style="height: 720px; border: 1px solid rgba(15, 23, 42, 0.12); border-radius: 12px; overflow: hidden;">
                         <iframe src="{{ $quoteEmbedUrl }}" style="width:100%;height:100%;border:0;" loading="lazy"></iframe>
                     </div>
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
                                 <select name="status" class="form-select">
                                     <option value="pending" @if($order->status=='pending') selected @endif>Chờ xử lý</option>
                                     <option value="processing" @if($order->status=='processing') selected @endif>Đang xử lý</option>
                                     <option value="completed" @if($order->status=='completed') selected @endif>Hoàn thành</option>
                                     <option value="cancelled" @if($order->status=='cancelled') selected @endif>Đã hủy</option>
                                 </select>
                             </div>
                             <div class="col-12 d-grid">
                                 <button type="submit" class="btn btn-success fw-bold">Cập nhật</button>
                             </div>
                         </div>
                     </form>

                     <hr>

                     <div class="d-flex justify-content-between align-items-center">
                         <div class="text-muted">Tổng tiền</div>
                         <div class="fw-bold" style="font-size:1.25rem; color:#ef4444;">{{ number_format($total, 0, ',', '.') }}đ</div>
                     </div>

                     <div class="row g-2 mt-3">
                         <div class="col-12 d-grid">
                             <a class="btn btn-outline-primary fw-bold" href="{{ $quoteUrl }}" target="_blank" rel="noopener">In</a>
                         </div>
                         <div class="col-12 d-grid">
                             <a class="btn btn-outline-secondary fw-bold" href="{{ $quoteUrl }}" target="_blank" rel="noopener">Xuất PDF</a>
                         </div>
                         <div class="col-12 d-grid">
                             @if($mailTo)
                                 <a class="btn btn-outline-success fw-bold" href="{{ $mailTo }}">Gửi email</a>
                             @else
                                 <button class="btn btn-outline-success fw-bold" type="button" disabled>Gửi email</button>
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