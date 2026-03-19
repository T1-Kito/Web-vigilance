<!-- Mobile Sidebar -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
    <div class="offcanvas-header bg-primary text-white">
        <h5 class="offcanvas-title d-flex align-items-center gap-2" id="mobileSidebarLabel">
            <i class="bi bi-fingerprint"></i>
            <img src="{{ asset('logovigilance.jpg') }}" alt="Logo" style="height: 30px; max-height: 30px; display: block;">
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="p-3">
            <h6 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-grid-3x3-gap"></i> DANH MỤC SẢN PHẨM
            </h6>
            <div class="list-group list-group-flush">
                @foreach($categories as $category)
                    @if($category->children->count() > 0)
                        <button type="button" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 py-3 rounded-3 mb-1" style="transition: all 0.3s;" data-bs-toggle="collapse" data-bs-target="#mobileCatChildren-{{ $category->id }}" aria-expanded="false" aria-controls="mobileCatChildren-{{ $category->id }}">
                            <i class="bi bi-{{ $category->icon ?? 'box' }} text-primary" style="font-size: 1.2em;"></i>
                            <span class="fw-medium">{{ $category->name }}</span>
                            <i class="bi bi-chevron-down ms-auto text-muted"></i>
                        </button>
                        <div class="collapse" id="mobileCatChildren-{{ $category->id }}">
                            <a href="{{ route('category.show', $category->slug) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 py-2 ps-5 rounded-3 mb-1" style="transition: all 0.3s;">
                                <i class="bi bi-box text-secondary"></i>
                                <span>Xem tất cả</span>
                            </a>
                            @foreach($category->children as $child)
                                <a href="{{ route('category.show', $child->slug) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 py-2 ps-5 rounded-3 mb-1" style="transition: all 0.3s;">
                                    <i class="bi bi-{{ $child->icon ?? 'box' }} text-secondary"></i>
                                    <span>{{ $child->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <a href="{{ route('category.show', $category->slug) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 py-3 rounded-3 mb-1" style="transition: all 0.3s;">
                            <i class="bi bi-{{ $category->icon ?? 'box' }} text-primary" style="font-size: 1.2em;"></i>
                            <span class="fw-medium">{{ $category->name }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
        
        <hr class="my-3">
        
        <div class="p-3">
            <h6 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-clock-history"></i> LỊCH SỬ ĐƠN HÀNG
            </h6>
            @auth
                <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 py-3 rounded-3 mb-1" style="transition: all 0.3s;">
                    <i class="bi bi-bag text-primary" style="font-size: 1.2em;"></i>
                    <span>Xem đơn hàng</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 py-3 rounded-3 mb-1" style="transition: all 0.3s;">
                    <i class="bi bi-person text-primary" style="font-size: 1.2em;"></i>
                    <span>Đăng nhập</span>
                </a>
            @endauth
        </div>
        
        <hr class="my-3">
        
        <div class="p-3">
            <h6 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check"></i> BẢO HÀNH
            </h6>
            <a href="{{ route('warranty.check') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 py-3 rounded-3 mb-1" style="transition: all 0.3s;">
                <i class="bi bi-search text-primary" style="font-size: 1.2em;"></i>
                <span>Tra cứu bảo hành</span>
            </a>
        </div>
        
        <hr class="my-3">
        
        <div class="p-3">
            <h6 class="fw-bold text-primary mb-3 d-flex align-items-center gap-2">
                <i class="bi bi-telephone"></i> LIÊN HỆ
            </h6>
            <a href="tel:0909123456" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 py-3 rounded-3 mb-1" style="transition: all 0.3s;">
                <i class="bi bi-telephone-fill text-success" style="font-size: 1.2em;"></i>
                <span>0909 123 456 - Tư vấn</span>
            </a>
            <a href="tel:0919006976" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 py-3 rounded-3 mb-1" style="transition: all 0.3s;">
                <i class="bi bi-person-lines-fill text-primary" style="font-size: 1.2em;"></i>
                <span>0919 006 976 - Kỹ thuật</span>
            </a>
        </div>
    </div>
</div> 