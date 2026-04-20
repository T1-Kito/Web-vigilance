@extends('layouts.user')

@section('title', 'Kết quả tìm kiếm')

@section('content')
@php
    $isAgentUser = auth()->check() && (string) auth()->user()->role === 'agent';
@endphp
<div class="search-results-page">
    <div class="row">
        <div class="col-md-3 d-none d-md-block">
            @include('components.sidebar', ['categories' => $categories])
        </div>
        <div class="col-12 col-md-9">
            <div class="d-md-none mb-3">
                <a href="javascript:history.back()" class="btn btn-outline-secondary">&larr; Quay lại</a>
            </div>
        @if($products->count())
        <div class="row g-4">
            @foreach($products as $product)
                @php
                    $countOnPage = $products->count();
                    $colClass = match (true) {
                        $countOnPage === 1 => 'col-12 col-sm-10 col-md-7 col-lg-6 col-xl-5 d-flex',
                        $countOnPage === 2 => 'col-12 col-sm-6 col-md-6 col-lg-5 col-xl-4 d-flex',
                        default => 'col-12 col-sm-6 col-md-4 col-lg-3 col-xl-5th d-flex',
                    };
                @endphp
                <div class="{{ $colClass }}">
                    <div class="card h-100 border-0 shadow product-card-modern w-100 position-relative">
                        @php
                            $listedPrice = (float) ($product->price ?? 0);
                            $agentPrice = (float) ($product->agency_price ?? 0);
                            $displayPrice = $isAgentUser && $agentPrice > 0 ? $agentPrice : $listedPrice;
                            $oldPrice = $product->old_price ?? null;
                            $discount = $oldPrice && $oldPrice > $listedPrice ? round(100 - $listedPrice / $oldPrice * 100) : null;
                            $showListedStrike = $isAgentUser && $agentPrice > 0 && $listedPrice > 0;
                        @endphp
                        @if($discount)
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2" style="font-size:0.95em; z-index:2;">Giảm {{ $discount }}%</span>
                        @endif
                        <div class="product-img-wrap d-flex align-items-center justify-content-center" style="height:210px; background:#fff; border-radius:1.5rem 1.5rem 0 0; overflow:hidden;">
                            <a href="{{ route('product.show', $product->slug) }}" class="d-block w-100 h-100">
                                <img src="{{ asset('images/products/' . $product->image) }}" class="product-img-modern" alt="{{ $product->name }}">
                            </a>
                        </div>
                        <div class="card-body d-flex flex-column p-3" style="flex:1 1 auto;">
                            <a href="{{ route('product.show', $product->slug) }}" style="text-decoration:none; color:inherit;">
                                <h6 class="card-title fw-bold mb-2" style="font-size:1.08em; min-height:44px; color:#222; line-height:1.25;">{{ $product->name }}</h6>
                            </a>
                            <div class="mb-1 text-muted" style="font-size:0.97em; min-height:18px;">{{ $product->category->name ?? '' }}</div>
                            <div class="mb-2">
                                @if($displayPrice == 0)
                                    <span class="fw-bold" style="color:#d32f2f; font-size:1.18em;">
                                        <a href="https://zalo.me/0982751039" target="_blank" style="text-decoration:none; color:inherit;">Liên hệ</a>
                                    </span>
                                @else
                                    <span class="fw-bold" style="color:#d32f2f; font-size:1.18em;">{{ number_format($displayPrice, 0, ',', '.') }}đ</span>
                                    @if($showListedStrike)
                                        <span class="text-decoration-line-through text-secondary ms-2" style="font-size:0.98em;">{{ number_format($listedPrice, 0, ',', '.') }}đ</span>
                                    @elseif($oldPrice && $oldPrice > $listedPrice)
                                        <span class="text-decoration-line-through text-secondary ms-2" style="font-size:0.98em;">{{ number_format($oldPrice, 0, ',', '.') }}đ</span>
                                    @endif
                                @endif
                            </div>
                            <div class="mb-2 text-truncate" style="font-size:0.97em; color:#444;" title="{{ $product->description }}">{{ Str::limit($product->description, 60) }}</div>
                            <a href="{{ route('product.show', $product->slug) }}" class="btn btn-modern-main w-100 fw-bold mt-auto d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links('pagination::bootstrap-4') }}
        </div>
        @else
            <div class="alert alert-warning mt-4">Không tìm thấy sản phẩm nào phù hợp với từ khóa <b>"{{ $q }}"</b>.</div>
        @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media (min-width: 1200px) {
  .col-xl-5th {
    flex: 0 0 20%;
    max-width: 20%;
  }
}

.search-results-page {
    padding-top: 6px;
}

.product-card-modern {
    border-radius: 18px;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08) !important;
    overflow: hidden;
}

.product-card-modern:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 34px rgba(15, 23, 42, 0.12) !important;
}

.product-img-wrap {
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%) !important;
    height: 240px !important;
    border-radius: 18px 18px 0 0 !important;
}

.product-img-modern {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 10px;
}

.product-card-modern .card-body {
    gap: 6px;
}

.product-card-modern .card-title {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endpush 