@extends('layouts.admin')

@section('title', 'Chi tiết hàng hóa')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
        <div class="d-flex align-items-start gap-2">
            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-light border" title="Quay lại">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="top-product-thumb-wrap">
                @if(!empty($product->image))
                    <button type="button" class="image-preview-trigger" data-image-url="{{ asset('images/products/' . $product->image) }}" data-image-alt="{{ $product->name }}" title="Xem ảnh lớn">
                        <img src="{{ asset('images/products/' . $product->image) }}" alt="{{ $product->name }}" class="top-product-thumb">
                    </button>
                @else
                    <div class="top-product-thumb-empty">No img</div>
                @endif
            </div>
            <div>
                <h1 class="h5 fw-bold mb-1">{{ $product->name }}</h1>
                <div class="text-muted small">
                    {{ $product->serial_number ?: ('SP-' . str_pad($product->id, 4, '0', STR_PAD_LEFT)) }}
                    · {{ optional($product->category)->name ?: 'Chưa phân loại' }}
                    · {{ $product->brand ?: 'Chưa có thương hiệu' }}
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.edit', $product->id) . '?return_url=' . urlencode(url()->current()) }}" class="btn btn-primary btn-sm">
                <i class="bi bi-pencil-square me-1"></i>Sửa
            </a>
        </div>
    </div>

    <div class="misa-tabs mb-3">
        <button type="button" class="misa-tab tab-link active" data-tab="tab-detail">Thông tin chi tiết</button>
        <button type="button" class="misa-tab tab-link" data-tab="tab-quote">Báo giá <span class="tab-badge">{{ $quoteCount ?? 0 }}</span></button>
        <button type="button" class="misa-tab tab-link" data-tab="tab-order">Đơn hàng/Hợp đồng <span class="tab-badge">{{ $salesOrderCount ?? 0 }}</span></button>
        <button type="button" class="misa-tab tab-link" data-tab="tab-invoice">Hóa đơn <span class="tab-badge">{{ $invoiceCount ?? 0 }}</span></button>
    </div>

    <div class="misa-card">
        <div class="misa-search-row">
            <input type="text" class="form-control form-control-sm" placeholder="Tìm kiếm trường" style="max-width: 220px;" disabled>
        </div>

        <div id="tab-detail" class="tab-pane active">
            <div class="misa-section">
            <h2 class="misa-section-title">Thông tin chung</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="misa-kv"><span>Mã hàng hóa</span><strong>{{ $product->serial_number ?: ('SP-' . str_pad($product->id, 4, '0', STR_PAD_LEFT)) }}</strong></div>
                    <div class="misa-kv"><span>Tên hàng hóa</span><strong>{{ $product->name }}</strong></div>
                    <div class="misa-kv"><span>Tính chất</span><strong>Hàng hóa</strong></div>
                    <div class="misa-kv"><span>Loại hàng hóa</span><strong>{{ optional($product->category)->name ?: '—' }}</strong></div>
                    <div class="misa-kv align-items-start">
                        <span>Diễn giải khi bán</span>
                        <div>
                            <div id="product-desc" class="misa-desc-clamp">{{ strip_tags($product->description ?? '') ?: '—' }}</div>
                            <button type="button" id="product-desc-toggle" class="btn btn-link btn-sm p-0 mt-1" style="text-decoration:none;">Xem thêm</button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="misa-kv"><span>Đơn vị tính chính</span><strong>{{ $product->unit_name ?: 'Cái' }}</strong></div>
                    <div class="misa-kv"><span>Nguồn gốc</span><strong>{{ $product->origin ?: '—' }}</strong></div>
                    <div class="misa-kv"><span>Kho ngầm định</span><strong>{{ $product->default_warehouse ?: '—' }}</strong></div>
                    <div class="misa-kv"><span>Thương hiệu</span><strong>{{ $product->brand ?: '—' }}</strong></div>
                    <div class="misa-kv"><span>Thương hiệu (hệ thống)</span><strong>—</strong></div>
                </div>
            </div>
        </div>

        <div class="misa-section">
            <h2 class="misa-section-title">Thông tin giá</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="misa-kv"><span>Đơn giá nhà máy</span><strong>{{ number_format((float) ($product->factory_price ?? $product->price), 0, ',', '.') }}</strong></div>
                    <div class="misa-kv"><span>Giá đề nghị bán đại lý</span><strong>{{ number_format((float) ($product->agency_suggested_price ?? $product->price), 0, ',', '.') }}</strong></div>
                    <div class="misa-kv"><span>Giá bán cho Đại Lý</span><strong>{{ number_format((float) ($product->agency_price ?? $product->price), 0, ',', '.') }}</strong></div>
                    <div class="misa-kv"><span>Giá bán cho Khách Lẻ</span><strong>{{ number_format((float) ($product->retail_price ?? $product->price), 0, ',', '.') }}</strong></div>
                    <div class="misa-kv"><span>Đơn giá vận chuyển</span><strong>{{ number_format((float) ($product->shipping_price ?? 0), 0, ',', '.') }}</strong></div>
                </div>
                <div class="col-lg-6">
                    <div class="misa-kv"><span>Đơn giá nhân công</span><strong>{{ number_format((float) ($product->labor_price ?? 0), 0, ',', '.') }}</strong></div>
                    <div class="misa-kv"><span>Thuế GTGT</span><strong>{{ rtrim(rtrim(number_format((float) ($product->vat_percent ?? 0), 2, '.', ''), '0'), '.') }}%</strong></div>
                    <div class="misa-kv"><span>Giá bán là đơn giá sau thuế</span><strong>{{ $product->price_includes_tax ? 'Có' : 'Không' }}</strong></div>
                    <div class="misa-kv"><span>Ngầm định ghi nhận DS trước thuế</span><strong>{{ $product->default_revenue_mode ?: '—' }}</strong></div>
                    <div class="misa-kv"><span>Giá vốn</span><strong>{{ number_format((float) ($product->cost_price ?? $product->price), 0, ',', '.') }}</strong></div>
                </div>
            </div>
        </div>

        <div class="misa-section">
            <h2 class="misa-section-title">Thông tin bảo hành</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="misa-kv"><span>Thời hạn bảo hành</span><strong>{{ (int) ($product->warranty_months ?? 12) }} Tháng</strong></div>
                </div>
                <div class="col-lg-6">
                    <div class="misa-kv"><span>Nội dung bảo hành</span><strong>{{ strip_tags($product->warranty_content ?? '') ?: '—' }}</strong></div>
                </div>
            </div>
        </div>

        <div class="misa-section">
            <h2 class="misa-section-title">Thông tin kích thước</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="misa-kv"><span>Chiều cao</span><strong>{{ rtrim(rtrim(number_format((float) ($product->height ?? 0), 2, '.', ''), '0'), '.') }}</strong></div>
                    <div class="misa-kv"><span>Chiều dài</span><strong>{{ rtrim(rtrim(number_format((float) ($product->length ?? 0), 2, '.', ''), '0'), '.') }}</strong></div>
                    <div class="misa-kv"><span>Trọng lượng</span><strong>{{ rtrim(rtrim(number_format((float) ($product->weight ?? 0), 2, '.', ''), '0'), '.') }}</strong></div>
                </div>
                <div class="col-lg-6">
                    <div class="misa-kv"><span>Chiều rộng</span><strong>{{ rtrim(rtrim(number_format((float) ($product->width ?? 0), 2, '.', ''), '0'), '.') }}</strong></div>
                    <div class="misa-kv"><span>Bán kính</span><strong>{{ rtrim(rtrim(number_format((float) ($product->radius ?? 0), 2, '.', ''), '0'), '.') }}</strong></div>
                </div>
            </div>
        </div>

        <div class="misa-section">
            <h2 class="misa-section-title">Thông tin mô tả</h2>
            <div class="misa-kv align-items-start">
                <span>Mô tả</span>
                <div>
                    <div id="product-desc-2" class="misa-desc-clamp">{{ strip_tags($product->description ?? '') ?: '—' }}</div>
                    <button type="button" id="product-desc-toggle-2" class="btn btn-link btn-sm p-0 mt-1" style="text-decoration:none;">Xem thêm</button>
                </div>
            </div>
        </div>

        <div class="misa-section">
            <h2 class="misa-section-title">Thông số kỹ thuật</h2>
            <div class="misa-kv align-items-start">
                <span>Thông số kỹ thuật</span>
                <div>
                    <div id="product-specs" class="misa-desc-clamp">{{ strip_tags($product->specifications ?? '') ?: '—' }}</div>
                    <button type="button" id="product-specs-toggle" class="btn btn-link btn-sm p-0 mt-1" style="text-decoration:none;">Xem thêm</button>
                </div>
            </div>
        </div>

        <div class="misa-section">
            <h2 class="misa-section-title">Thông tin hệ thống</h2>
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="misa-kv"><span>Chủ sở hữu</span><strong>{{ optional(auth()->user())->name ?: '—' }}</strong></div>
                    <div class="misa-kv"><span>Người tạo</span><strong>{{ optional(auth()->user())->name ?: 'SME' }}</strong></div>
                    <div class="misa-kv"><span>Người sửa</span><strong>{{ optional(auth()->user())->name ?: '—' }}</strong></div>
                    <div class="misa-kv"><span>Dùng chung</span><strong>✓</strong></div>
                    <div class="misa-kv"><span>Bố cục</span><strong>Mẫu tiêu chuẩn</strong></div>
                </div>
                <div class="col-lg-6">
                    <div class="misa-kv"><span>Đơn vị</span><strong>Công ty Cổ phần Vigilance</strong></div>
                    <div class="misa-kv"><span>Ngày tạo</span><strong>{{ optional($product->created_at)->format('d/m/Y H:i') ?: '—' }}</strong></div>
                    <div class="misa-kv"><span>Ngày sửa</span><strong>{{ optional($product->updated_at)->format('d/m/Y H:i') ?: '—' }}</strong></div>
                    <div class="misa-kv"><span>Ngừng theo dõi</span><strong>{{ $product->status ? 'Không' : 'Có' }}</strong></div>
                    <div class="misa-kv"><span>Là combo hàng hoá</span><strong>Không</strong></div>
                </div>
            </div>
        </div>

        @if($product->images->count())
            <div class="misa-section">
                <h2 class="misa-section-title">Ảnh đính kèm</h2>
                <div class="row g-2">
                    @foreach($product->images as $img)
                        <div class="col-6 col-md-3 col-lg-2">
                            <button type="button" class="image-preview-trigger image-thumb-trigger" data-image-url="{{ asset('images/products/' . $img->image_path) }}" data-image-alt="{{ $img->alt_text ?: $product->name }}" title="Xem ảnh lớn">
                                <img src="{{ asset('images/products/' . $img->image_path) }}" alt="{{ $img->alt_text ?: $product->name }}" class="img-fluid rounded border" style="height:100px; object-fit:cover; width:100%;">
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        </div>

        <div id="tab-quote" class="tab-pane">
            <div class="misa-section">
                <h2 class="misa-section-title">Báo giá ({{ $quoteCount ?? 0 }})</h2>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Số báo giá</th>
                                <th>Khách hàng</th>
                                <th>Ngày báo giá</th>
                                <th>Đơn vị tính</th>
                                <th class="text-end">Số lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($quoteItems ?? collect()) as $item)
                                <tr class="table-row-link" data-href="{{ optional($item->quote) ? route('admin.quotes.show', $item->quote->id) : '' }}">
                                    <td>{{ optional($item->quote)->quote_code ?: ('BG-' . $item->quote_id) }}</td>
                                    <td>{{ optional($item->quote)->invoice_company_name ?: optional($item->quote)->receiver_name ?: '—' }}</td>
                                    <td>{{ optional(optional($item->quote)->created_at)->format('d/m/Y') ?: '—' }}</td>
                                    <td>{{ $item->unit ?: ($product->unit_name ?: 'Cái') }}</td>
                                    <td class="text-end">{{ number_format((float) $item->quantity, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) $item->price, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) ($item->quantity * $item->price), 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">Chưa có báo giá cho sản phẩm này</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="tab-order" class="tab-pane">
            <div class="misa-section">
                <h2 class="misa-section-title">Đơn hàng/Hợp đồng ({{ $salesOrderCount ?? 0 }})</h2>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Số đơn hàng</th>
                                <th>Khách hàng</th>
                                <th>Ngày tạo</th>
                                <th>Đơn vị tính</th>
                                <th class="text-end">Số lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($salesOrderItems ?? collect()) as $item)
                                <tr class="table-row-link" data-href="{{ optional($item->salesOrder) ? route('admin.sales-orders.show', $item->salesOrder->id) : '' }}">
                                    <td>{{ optional($item->salesOrder)->sales_order_code ?: ('SO-' . $item->sales_order_id) }}</td>
                                    <td>{{ optional($item->salesOrder)->invoice_company_name ?: optional($item->salesOrder)->receiver_name ?: '—' }}</td>
                                    <td>{{ optional(optional($item->salesOrder)->created_at)->format('d/m/Y') ?: '—' }}</td>
                                    <td>{{ $item->unit ?: ($product->unit_name ?: 'Cái') }}</td>
                                    <td class="text-end">{{ number_format((float) $item->quantity, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) ($item->quantity * $item->unit_price), 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">Chưa có đơn hàng/hợp đồng cho sản phẩm này</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="tab-invoice" class="tab-pane">
            <div class="misa-section">
                <h2 class="misa-section-title">Hóa đơn ({{ $invoiceCount ?? 0 }})</h2>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Số hóa đơn</th>
                                <th>Ngày xuất</th>
                                <th>Trạng thái</th>
                                <th>Đơn vị tính</th>
                                <th class="text-end">Số lượng</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($invoiceItems ?? collect()) as $item)
                                <tr class="table-row-link" data-href="{{ optional($item->invoice) ? route('admin.invoices.show', $item->invoice->id) : '' }}">
                                    <td>{{ optional($item->invoice)->invoice_code ?: ('HD-' . $item->invoice_id) }}</td>
                                    <td>{{ optional(optional($item->invoice)->issued_at)->format('d/m/Y') ?: optional(optional($item->invoice)->created_at)->format('d/m/Y') ?: '—' }}</td>
                                    <td>{{ optional($item->invoice)->status ?: '—' }}</td>
                                    <td>{{ $item->unit ?: ($product->unit_name ?: 'Cái') }}</td>
                                    <td class="text-end">{{ number_format((float) $item->quantity, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format((float) ($item->line_total ?? ($item->quantity * $item->unit_price)), 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-3">Chưa có hóa đơn cho sản phẩm này</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.misa-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}

.top-product-thumb-wrap {
    width: 52px;
    height: 52px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: #fff;
    overflow: hidden;
    flex: 0 0 52px;
}

.top-product-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.top-product-thumb-empty {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    color: #9ca3af;
}

.misa-tabs {
    display: flex;
    gap: 2px;
    border-bottom: 1px solid #e5e7eb;
    overflow-x: auto;
    white-space: nowrap;
}

.misa-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 14px;
    color: #4b5563;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    font-size: 13px;
    background: transparent;
    border: none;
}

.misa-tab.active {
    color: #2563eb;
    border-bottom: 2px solid #2563eb;
    font-weight: 600;
}

.tab-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 6px;
    border-radius: 999px;
    background: #e5e7eb;
    color: #374151;
    font-size: 11px;
    font-weight: 600;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.table-row-link {
    cursor: pointer;
}

.table-row-link:hover td {
    background: #e8f3ff !important;
    color: #0d6efd;
}

.misa-search-row {
    padding: 10px 12px;
    border-bottom: 1px solid #f1f5f9;
}

.misa-section {
    padding: 14px 12px;
    border-top: 1px solid #f1f5f9;
}

.misa-section-title {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 12px;
}

.misa-kv {
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 10px;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px solid #f3f4f6;
}

.misa-kv span {
    color: #6b7280;
    font-size: 13px;
}

.misa-kv strong {
    color: #111827;
    font-size: 13px;
    font-weight: 500;
}

.misa-desc-clamp {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
    color: #111827;
    font-size: 13px;
    line-height: 1.5;
    white-space: normal;
}

.misa-desc-clamp.expanded {
    display: block;
    -webkit-line-clamp: unset;
    overflow: visible;
}

@media (max-width: 768px) {
    .misa-kv {
        grid-template-columns: 1fr;
        gap: 2px;
    }
}

.image-preview-trigger {
    border: none;
    background: transparent;
    padding: 0;
    width: 100%;
    height: 100%;
    cursor: zoom-in;
}

.image-thumb-trigger {
    display: block;
}

#productImagePreviewModal .modal-body {
    max-height: 70vh;
    overflow: auto;
}

#productImagePreview {
    max-width: 100%;
    max-height: 65vh;
    object-fit: contain;
}
</style>

<div class="modal fade" id="productImagePreviewModal" tabindex="-1" aria-labelledby="productImagePreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productImagePreviewTitle">Xem ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="productImagePreview" src="" alt="Ảnh sản phẩm">
            </div>
            <div class="modal-footer justify-content-between">
                <a id="productImagePreviewOpenNew" href="#" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">Mở tab mới</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Quay lại</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function wireClamp(contentId, buttonId) {
        const content = document.getElementById(contentId);
        const button = document.getElementById(buttonId);
        if (!content || !button) return;

        const hasOverflow = content.scrollHeight > content.clientHeight + 2;
        if (!hasOverflow) {
            button.style.display = 'none';
            return;
        }

        button.addEventListener('click', function () {
            const expanded = content.classList.toggle('expanded');
            button.textContent = expanded ? 'Thu gọn' : 'Xem thêm';
        });
    }

    wireClamp('product-desc', 'product-desc-toggle');
    wireClamp('product-desc-2', 'product-desc-toggle-2');
    wireClamp('product-specs', 'product-specs-toggle');

    const tabLinks = document.querySelectorAll('.tab-link');
    const tabPanes = document.querySelectorAll('.tab-pane');
    tabLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            const target = link.getAttribute('data-tab');
            tabLinks.forEach(function (l) { l.classList.remove('active'); });
            tabPanes.forEach(function (p) { p.classList.remove('active'); });
            link.classList.add('active');
            const pane = document.getElementById(target);
            if (pane) pane.classList.add('active');
        });
    });

    const rowLinks = document.querySelectorAll('.table-row-link');
    rowLinks.forEach(function (row) {
        row.addEventListener('click', function (event) {
            const target = event.target;
            if (target && (target.closest('a') || target.closest('button') || target.closest('input') || target.closest('select'))) {
                return;
            }
            const href = row.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });

    const modalEl = document.getElementById('productImagePreviewModal');
    const previewImg = document.getElementById('productImagePreview');
    const openNewLink = document.getElementById('productImagePreviewOpenNew');
    const triggers = document.querySelectorAll('.image-preview-trigger');

    if (modalEl && previewImg && openNewLink && typeof bootstrap !== 'undefined') {
        const imageModal = new bootstrap.Modal(modalEl);
        triggers.forEach(function (btn) {
            btn.addEventListener('click', function () {
                const imageUrl = btn.getAttribute('data-image-url') || '';
                const imageAlt = btn.getAttribute('data-image-alt') || 'Ảnh sản phẩm';
                previewImg.src = imageUrl;
                previewImg.alt = imageAlt;
                openNewLink.href = imageUrl;
                imageModal.show();
            });
        });
    }
});
</script>
@endsection
