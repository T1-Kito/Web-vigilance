@extends('layouts.user')
@section('title', 'Sản phẩm yêu thích')
@section('content')
<main class="container py-4 bg-white rounded-4 shadow-lg" style="min-height: 500px; margin-top: 24px;">
    <h2 class="fw-bold mb-4" style="color:#007BFF; font-size:1.5rem;">Sản phẩm yêu thích</h2>
    @if($wishlists->count())
    <div class="row g-4 justify-content-start">
        @foreach($wishlists as $item)
            @php $product = $item->product; @endphp
            @if($product)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-5th d-flex">
                <div class="card h-100 border-0 shadow product-card-modern w-100 position-relative">
                    <div class="position-absolute top-0 end-0 m-2" style="z-index:3;">
                        <button type="button" class="btn btn-light p-1 wishlist-btn" data-product-id="{{ $product->id }}" style="border-radius:50%; box-shadow:0 2px 8px #007bff22;">
                            <i class="bi bi-heart-fill text-danger" style="font-size:1.35em;"></i>
                        </button>
                    </div>
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
                            @if($product->price == 0)
                                <span class="fw-bold" style="color:#d32f2f; font-size:1.18em;">
                                    <a href="https://zalo.me/0982751039" target="_blank" style="text-decoration:none; color:inherit;">Liên hệ</a>
                                </span>
                            @else
                                <span class="fw-bold" style="color:#d32f2f; font-size:1.18em;">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                            @endif
                        </div>
                        <div class="mb-2 text-truncate" style="font-size:0.97em; color:#444;" title="{{ $product->description }}">{{ Str::limit($product->description, 60) }}</div>
                        <a href="{{ route('product.show', $product->slug) }}" class="btn btn-modern-main w-100 fw-bold mt-auto d-flex align-items-center justify-content-center gap-2">
                            Xem chi tiết
                        </a>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @else
        <div class="alert alert-info">Bạn chưa có sản phẩm yêu thích nào.</div>
    @endif
</main>
@endsection
@auth
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.wishlist-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var productId = this.getAttribute('data-product-id');
            var card = this.closest('.col-12, .col-sm-6, .col-md-4, .col-lg-3, .col-xl-5th');
            fetch("{{ route('wishlist.toggle') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'removed') {
                    // Ẩn card khỏi danh sách
                    if(card) card.style.display = 'none';
                }
            });
        });
    });
});
</script>
@endauth 