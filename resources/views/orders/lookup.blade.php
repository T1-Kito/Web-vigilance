@extends('layouts.user')

@section('title', 'Tra cứu đơn hàng')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4" style="color:#007BFF;">Tra cứu đơn hàng</h2>

    <style>
        .olp-card {
            border: 1px solid rgba(15, 23, 42, 0.10);
            border-radius: 16px;
        }
        .olp-card .card-body { padding: 16px; }
        .olp-kv {
            display: grid;
            grid-template-columns: 140px 1fr;
            gap: 6px 14px;
        }
        @media (max-width: 575.98px) {
            .olp-kv { grid-template-columns: 1fr; }
        }
        .olp-k { color: rgba(15, 23, 42, 0.70); font-weight: 700; }
        .olp-v { color: #0f172a; font-weight: 700; }
        .olp-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.85rem;
            border: 1px solid rgba(15, 23, 42, 0.12);
            background: #fff;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        .olp-pill--pending { background: rgba(245, 158, 11, 0.12); border-color: rgba(245, 158, 11, 0.25); color: #b45309; }
        .olp-pill--success { background: rgba(34, 197, 94, 0.12); border-color: rgba(34, 197, 94, 0.25); color: #15803d; }
        .olp-pill--danger { background: rgba(239, 68, 68, 0.10); border-color: rgba(239, 68, 68, 0.25); color: #b91c1c; }
        .olp-pill--info { background: rgba(59, 130, 246, 0.10); border-color: rgba(59, 130, 246, 0.22); color: #1d4ed8; }

        .olp-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 0.86rem;
            border: 1px solid rgba(37, 99, 235, 0.25);
            background: #fff;
            color: #2563eb;
            text-decoration: none;
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.10);
            transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
            white-space: nowrap;
        }
        .olp-btn:hover {
            background: rgba(37, 99, 235, 0.06);
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(37, 99, 235, 0.14);
            color: #1d4ed8;
        }
        .olp-btn:active { transform: translateY(0); }
        .olp-btn i { font-size: 1rem; line-height: 1; }
        .olp-table th { font-size: 0.92rem; color: rgba(15,23,42,0.70); }
        .olp-table td { vertical-align: middle; }
        .olp-table tfoot th { background: rgba(15,23,42,0.03); }
    </style>

    @if(session('lookup_error'))
        <div class="alert alert-danger">{{ session('lookup_error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm mb-4 olp-card">
        <div class="card-body">
            <form method="POST" action="{{ route('orders.lookup.search') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-12 col-md-9">
                    <label class="form-label fw-semibold">Mã đơn hàng</label>
                    <input type="text" name="order_code" class="form-control" placeholder="VD: OD250305ABC123" value="{{ old('order_code', request('order_code')) }}" required>
                </div>
                <div class="col-12 col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-search"></i> Tra cứu
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($order)
        @php
            $orderCode = $order->order_code ?? ("VK" . str_pad($order->id, 6, '0', STR_PAD_LEFT));
            $items = $order->items ?? collect();
            $total = (int) $items->sum(function ($i) {
                return (int) ($i->price ?? 0) * (int) ($i->quantity ?? 0);
            });
            $statusKey = $order->status;
            $statusLabel = match($statusKey) {
                'pending' => 'Đang báo giá',
                'processing' => 'Đang xử lý',
                'shipping' => 'Đang giao',
                'completed' => 'Hoàn tất',
                'cancelled' => 'Đã hủy',
                default => (string) $statusKey,
            };
            $statusClass = match ($statusKey) {
                'completed' => 'olp-pill--success',
                'cancelled' => 'olp-pill--danger',
                'processing', 'shipping' => 'olp-pill--info',
                default => 'olp-pill--pending',
            };
        @endphp

        <div class="card shadow-sm olp-card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="fw-bold" style="font-size: 1.05rem; color:#0f172a;">Đơn hàng {{ $orderCode }}</div>
                        <div class="text-muted" style="font-size:0.9rem;">
                            Ngày đặt: {{ optional($order->created_at)->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a class="olp-btn" href="{{ route('orders.quote', ['orderCode' => $order->order_code]) }}" target="_blank" rel="noopener">
                            <i class="bi bi-file-earmark-text"></i>
                            <span>Xem báo giá</span>
                        </a>
                        <span class="olp-pill {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                </div>

                <hr class="my-3">

                <div class="row g-3">
                    <div class="col-12 col-lg-7">
                        <div class="fw-bold mb-2" style="color:#0f172a;">Thông tin nhận hàng</div>
                        <div class="olp-kv">
                            <div class="olp-k">Người nhận</div>
                            <div class="olp-v">{{ $order->receiver_name }}</div>
                            <div class="olp-k">Số điện thoại</div>
                            <div class="olp-v">{{ $order->receiver_phone }}</div>
                            <div class="olp-k">Địa chỉ</div>
                            <div class="olp-v">{{ $order->receiver_address }}</div>
                            @if($order->note)
                                <div class="olp-k">Ghi chú</div>
                                <div class="olp-v">{{ $order->note }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-lg-5">
                        <div class="fw-bold mb-2" style="color:#0f172a;">Thông tin đơn</div>
                        <div class="olp-kv">
                            <div class="olp-k">Tổng tiền</div>
                            <div class="olp-v">{{ number_format($total, 0, ',', '.') }}đ</div>
                            <div class="olp-k">Số sản phẩm</div>
                            <div class="olp-v">{{ $items->count() }}</div>
                            <div class="olp-k">Trạng thái</div>
                            <div class="olp-v">{{ $statusLabel }}</div>
                        </div>
                    </div>
                </div>

                
            </div>
        </div>
    @endif
</div>
@endsection
