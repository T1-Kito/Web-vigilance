@extends('layouts.user')

@section('title', $product->name . ' - Vigilance')

@php
    $productDescription = $product->description ?: ($product->information ?: ($product->specifications ?: ''));
    $productDescription = trim(strip_tags($productDescription));
@endphp

@section('meta_description', $productDescription ? \Illuminate\Support\Str::limit($productDescription, 155) : ($product->name . ' chính hãng, giá tốt, tư vấn lắp đặt và bảo hành rõ ràng tại Vigilance.'))
@section('canonical', url('/product/' . $product->slug))

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'sku' => $product->serial_number ?: null,
    'brand' => $product->brand ? [
        '@type' => 'Brand',
        'name' => $product->brand,
    ] : null,
    'image' => $product->image ? [asset($product->image)] : null,
    'description' => $productDescription ?: null,
    'offers' => is_numeric($product->price) ? [
        '@type' => 'Offer',
        'url' => url('/product/' . $product->slug),
        'priceCurrency' => 'VND',
        'price' => (string) ((int) $product->price),
        'availability' => 'https://schema.org/InStock',
    ] : null,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

<style>
/* Style A (clean/minimal) overrides */
:root {
    --pd-radius: 14px;
    --pd-border: rgba(15, 23, 42, 0.10);
    --pd-shadow-sm: 0 10px 28px rgba(2, 6, 23, 0.06);
    --pd-shadow-md: 0 14px 36px rgba(2, 6, 23, 0.10);
    --pd-muted: #64748b;
}

/* Make surfaces feel unified */
.pd-card,
.product-info-card,
.category-sidebar,
.tab-content,
.bg-white.rounded.shadow-sm {
    border-radius: var(--pd-radius) !important;
    border-color: var(--pd-border) !important;
}

/* Reduce “jumpiness” */
.product-info-card:hover,
.product-gallery:hover,
.commit-card:hover {
    transform: none !important;
}

/* Commitments: make them look like one coherent section */
.product-commitments .rounded-3 {
    background: #fff !important;
    border: 1px solid var(--pd-border) !important;
}

.product-commitments .rounded-2 {
    background: rgba(227, 0, 25, 0.10) !important;
}

.product-commitments .rounded-2 .bi {
    color: var(--brand-primary) !important;
}

/* CTA buttons: cleaner, less “banner-like” */
.btn-mobile {
    text-transform: none !important;
    letter-spacing: 0.2px !important;
}

.btn-mobile span {
    font-size: 1.02em;
}

/* Badges inside buybox: make them subtle */
.pd-buybox .badge {
    border-radius: 999px !important;
    font-weight: 700 !important;
    padding: 6px 10px !important;
    letter-spacing: 0 !important;
}

/* Color options: reduce “heavy card” feeling */
.pd-buybox .color-option-btn {
    box-shadow: 0 6px 16px rgba(2, 6, 23, 0.08) !important;
    border-radius: 14px !important;
    border-width: 1px !important;
}

/* Addons: neutral border + cleaner hover */
.addon-card {
    box-shadow: none !important;
}

.addon-card:hover {
    box-shadow: none !important;
}

#addon-block .addon-card {
    background: #fff !important;
    border: 1px solid rgba(15, 23, 42, 0.10) !important;
    border-radius: 14px !important;
    padding: 12px 12px !important;
    box-shadow: 0 8px 18px rgba(2, 6, 23, 0.06) !important;
    position: relative;
    min-height: 104px;
}

#addon-block .addon-card .addon-checkbox {
    position: absolute;
    top: 12px;
    left: 12px;
    margin: 0 !important;
    z-index: 2;
}

#addon-block .addon-card .addon-thumb {
    width: 88px;
    height: 88px;
    object-fit: contain;
    padding: 8px;
    border-radius: 14px;
    border: 1px solid rgba(15,23,42,0.10);
    background: #f8fafc;
}

#addon-block .addon-card .addon-title {
    font-size: 0.92rem;
    color: #0f172a;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

#addon-block .addon-card .addon-sub {
    margin-top: 6px;
    color: #e30019;
    font-size: 0.82rem;
    line-height: 1.2;
}

#addon-block .addon-scroll .addon-card > .d-flex {
    align-items: center;
}

@media (max-width: 576px) {
    #addon-block .addon-card {
        min-height: 98px;
    }

    #addon-block .addon-card .addon-thumb {
        width: 78px;
        height: 78px;
        padding: 7px;
        border-radius: 12px;
    }
}

#addon-block .addon-scroll {
    display: flex !important;
    flex-wrap: nowrap !important;
    gap: 12px !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    padding: 6px 6px 10px !important;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
}

#addon-block .addon-scroll::-webkit-scrollbar {
    height: 8px;
}

#addon-block .addon-scroll::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.06);
    border-radius: 999px;
}

#addon-block .addon-scroll::-webkit-scrollbar-thumb {
    background: rgba(15, 23, 42, 0.18);
    border-radius: 999px;
}

#addon-block .addon-scroll .addon-card {
    scroll-snap-align: start;
    flex: 0 0 auto;
    width: 280px;
    margin: 0 !important;
}

@media (max-width: 576px) {
    #addon-block .addon-scroll .addon-card {
        width: 240px;
    }
}

#addon-block .addon-scroll-wrap {
    position: relative;
}

#addon-block .addon-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, 0.12);
    background: rgba(255, 255, 255, 0.92);
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    box-shadow: 0 10px 24px rgba(2, 6, 23, 0.10);
}

#addon-block .addon-nav:hover {
    background: #fff;
}

#addon-block .addon-nav--prev { left: -10px; }
#addon-block .addon-nav--next { right: -10px; }

#addon-block .addon-scroll .addon-card:last-child {
    border-bottom: none !important;
}

/* Quick contact buttons under buybox CTA */
.pd-quick-contact {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

@media (min-width: 768px) {
    .pd-quick-contact {
        grid-template-columns: 1fr 1fr;
    }
}

.pd-buybox-extras {
    margin-top: 10px;
}

/* Neutralize Bootstrap padding utilities inside this block */
.pd-buybox-extras .p-3 {
    padding: 0 !important;
}

/* Controlled padding for each section */


.pd-buybox-extras__section + .pd-buybox-extras__section {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px dashed rgba(15, 23, 42, 0.12);
}

.pd-cta-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
}

.pd-buy-row {
    display: flex;
    align-items: stretch;
    gap: 16px;
    flex-wrap: wrap;
}

.pd-buy-row .pd-qty-stepper {
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(15,23,42,0.14);
    border-radius: 10px;
    overflow: hidden;
    background: #f8fafc;
    flex: 0 0 auto;
}

.pd-buy-row .pd-qty-btn {
    height: 40px;
    width: 32px;
    border: 0;
    background: transparent;
    color: #0f172a;
    font-weight: 900;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.pd-buy-row .pd-qty-btn:hover {
    background: rgba(15, 23, 42, 0.04);
}

.pd-buy-row .pd-qty-input {
    width: 36px;
    height: 40px;
    border: 0;
    text-align: center;
    font-weight: 800;
    color: #0f172a;
    outline: none;
    background: transparent;
    font-size: 0.9rem;
}

/* Hide native number input spinners (we use custom +/- buttons) */
.pd-buy-row .pd-qty-input::-webkit-outer-spin-button,
.pd-buy-row .pd-qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.pd-buy-row .pd-qty-input[type=number] {
    -moz-appearance: textfield;
    appearance: textfield;
}

.pd-buy-row .pd-qty-stepper {
    box-shadow:
        0 10px 26px rgba(15, 23, 42, 0.08),
        inset 0 0 0 1px rgba(255,255,255,0.8);
}

.pd-buy-row .pd-buy-btn {
    height: 44px;
    border-radius: 12px;
    flex: 1 1 0;
}

@media (min-width: 768px) {
    .pd-buy-row {
        flex-wrap: nowrap;
    }
}

@media (max-width: 576px) {
    .pd-buy-row .pd-buy-btn {
        flex: 1 1 100%;
    }
}

@media (min-width: 768px) {
    .pd-cta-row {
        grid-template-columns: 1fr 1fr;
    }
}

.pd-quick-contact a {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    border-radius: 12px;
    text-decoration: none;
    line-height: 1.1;
    background: #fff;
    color: #0f172a;
    transition: box-shadow 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
}

.pd-quick-contact a:hover {
    border-color: rgba(15, 23, 42, 0.16);
    box-shadow: 0 10px 24px rgba(2, 6, 23, 0.08);
    transform: translateY(-1px);
}

.pd-quick-contact .pd-qc-title {
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.pd-quick-contact .pd-qc-number {
    font-size: 0.98rem;
    letter-spacing: 0.2px;
}

/* Accent per button type */
.pd-qc-hotline { --qc-accent: #e30019; }
.pd-qc-chat { --qc-accent: #16a34a; }
.pd-qc-zalo { --qc-accent: #1d4ed8; }
.pd-qc-tech { --qc-accent: #2563eb; }

/* Hierarchy: Hotline primary, others secondary */
/* (2x2 layout) keep all items occupying one grid cell */

.pd-qc-hotline {
    background: color-mix(in srgb, var(--qc-accent) 10%, #ffffff);
    border-color: color-mix(in srgb, var(--qc-accent) 28%, #ffffff);
}

.pd-qc-hotline .pd-qc-title,
.pd-qc-hotline .pd-qc-number {
    color: #0f172a;
}

.pd-qc-chat,
.pd-qc-zalo,
.pd-qc-tech {
    background: #fff;
    
}

.pd-quick-contact .pd-qc-icon {
    width: 44px;
    height: 44px;
    border-radius: 0;
    background: transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    color: var(--qc-accent, #0f172a);
}

.pd-quick-contact .pd-qc-icon img {
    width: 100% !important;
    height: 100% !important;
    object-fit: contain;
}

.pd-quick-contact .pd-qc-icon .bi {
    font-size: 1.35rem;
    line-height: 1;
}

.pd-quick-contact .pd-qc-text { text-align: left; }

@media (max-width: 576px) {
    .pd-quick-contact {
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }

    .pd-quick-contact a {
        padding: 11px 10px;
    }

    .pd-quick-contact .pd-qc-number {
        font-size: 0.92rem;
    }

    .pd-price-row {
        flex-wrap: wrap;
        gap: 10px;
    }

    .pd-price-vat {
        white-space: normal;
        flex: 0 0 100%;
        margin-top: 6px;
    }
}

/* Addon section header/link: neutral (less blue) */
#addon-block .fw-bold {
    color: #0f172a !important;
}

#addon-block a {
    color: #0f172a !important;
    text-decoration: none;
    opacity: 0.75;
}

#addon-block a:hover {
    opacity: 1;
}

/* Reduce excessive spacing around addon block */
#addon-block {
    margin-top: 16px !important;
}

/* Addon combo layout (sample-like) */
.addon-combo {
    background: #fff;
    box-shadow: var(--pd-shadow-sm);
    overflow: hidden;
}

.addon-combo__head {
    background: #f3f6ff;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
}

.addon-combo__badge {
    background: #e30019;
    color: #fff;
    font-weight: 800;
    border-radius: 10px;
    padding: 6px 10px;
    font-size: 0.85rem;
    line-height: 1;
}

.addon-combo__grid {
    display: grid;
    grid-template-columns: 1fr auto 1fr auto;
    gap: 14px;
    align-items: center;
}

.addon-combo__product {
    display: grid;
    grid-template-columns: 86px 1fr;
    gap: 12px;
    align-items: center;
    min-width: 0;
}

.addon-combo__thumb {
    width: 86px;
    height: 86px;
    border-radius: 14px;
    border: 1px solid rgba(15, 23, 42, 0.10);
    object-fit: cover;
    background: #fff;
}

.addon-combo__name {
    font-weight: 700;
    color: #0f172a;
    font-size: 0.95rem;
    line-height: 1.25;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.addon-combo__price {
    font-weight: 900;
    color: #0f172a;
    margin-top: 6px;
}

.addon-combo__op {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    border: 1px solid rgba(15, 23, 42, 0.10);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    background: #fff;
    font-weight: 900;
}

.addon-combo__savebox {
    width: 128px;
    height: 128px;
    border-radius: 14px;
    background: #eafaf0;
    border: 1px solid rgba(22, 163, 74, 0.18);
    display: grid;
    place-items: center;
    text-align: center;
    padding: 10px;
}

.addon-combo__savebox strong { color: #16a34a; font-size: 0.9rem; }
.addon-combo__savebox div { color: #0f172a; font-weight: 900; }

.addon-combo__list {
    
}

.addon-combo__totals {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding: 12px 16px 16px;
    border-top: 1px solid rgba(15, 23, 42, 0.08);
    background: #fff;
}

.addon-combo__totals .muted { color: #64748b; font-size: 0.9rem; }
.addon-combo__totals .total { font-weight: 900; font-size: 1.2rem; color: #0f172a; }
.addon-combo__totals .save { font-weight: 800; color: #16a34a; }

@media (max-width: 576px) {
    .addon-combo__grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .addon-combo__savebox {
        width: 100%;
        height: auto;
        padding: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .addon-combo__op { display: none; }
    .addon-combo__thumb { width: 72px; height: 72px; }
    .addon-combo__product { grid-template-columns: 72px 1fr; }
}

/* Gallery container: unify radius/shadow */
.main-image-container.pd-card {
    border-radius: 16px;
    border: 1px solid var(--pd-border);
    box-shadow: var(--pd-shadow);
}

/* Constrain overall page width for a more premium look */
.pd-page {
    max-width: 1200px;
}

/* Gallery: clean white frame + consistent thumbnails */
.product-gallery .main-image-container {
    background: #fff;
    border: 1px solid var(--pd-border);
}

.product-gallery .thumbnail-container {
    gap: 10px !important;
}

.product-gallery .product-thumbnail-img {
    width: 72px !important;
    height: 72px !important;
    border-radius: 12px !important;
    border: 1px solid rgba(15, 23, 42, 0.12) !important;
    object-fit: cover !important;
}

.product-gallery .thumbnail-wrapper .active-indicator {
    display: none !important;
}

.product-gallery .thumbnail-wrapper.is-active .product-thumbnail-img {
    border-color: var(--brand-primary) !important;
    box-shadow: 0 8px 18px rgba(227, 0, 25, 0.12) !important;
}

.product-gallery .thumbnail-wrapper:hover .product-thumbnail-img {
    border-color: rgba(15, 23, 42, 0.18) !important;
}

/* Related promotions under gallery */
.pd-related-promos {
    margin-top: 14px;
}

.pd-related-promos .pd-card {
    border-radius: 16px;
}

.pd-related-promos__banner img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 14px;
    border: 1px solid rgba(15, 23, 42, 0.10);
}

.pd-related-promos__title {
    font-weight: 600;
    font-size: 1rem;
    color: #0f172a;
}

.pd-related-promos__list {
    margin: 10px 0 0;
    padding-left: 18px;
    color: #334155;
    line-height: 1.6;
    font-size: 0.92rem;
}

.pd-related-promos__list li + li {
    margin-top: 8px;
}

.pd-related-promos__code {
    color: #0f172a;
}

@media (max-width: 576px) {
    .pd-related-promos__list {
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .product-gallery .product-thumbnail-img {
        width: 64px !important;
        height: 64px !important;
        border-radius: 10px !important;
    }
}

/* Addons: selected state highlight */
#addon-block .addon-card.is-selected {
    border-color: color-mix(in srgb, var(--brand-primary) 45%, rgba(15, 23, 42, 0.10)) !important;
    box-shadow: 0 10px 24px rgba(227, 0, 25, 0.10) !important;
}

#addon-block .addon-card.is-selected .addon-sub {
    color: var(--brand-primary);
}

/* Sidebar title: neutral, not bright blue */
.category-sidebar .fw-bold {
    color: #0f172a !important;
    border-bottom-color: rgba(15, 23, 42, 0.10) !important;
}

/* Make borders subtle on small elements */
.commit-card,
.category-sidebar {
    border-color: var(--pd-border) !important;
}

/* Sticky buy box on desktop for a more premium ecom feel */
@media (min-width: 992px) {
    .pd-buybox {
        position: sticky;
        top: 96px;
    }
}

@media (max-width: 576px) {
    .pd-card,
    .product-info-card,
    .tab-content,
    .bg-white.rounded.shadow-sm {
        border-radius: 12px !important;
        box-shadow: 0 8px 22px rgba(2, 6, 23, 0.06) !important;
    }

    .btn-mobile {
        padding: 14px 0 !important;
        font-size: 1.05em !important;
    }

    .pd-buybox .color-option-btn {
        min-width: 140px !important;
        min-height: 70px !important;
        padding: 12px 14px !important;
    }

    .pd-buybox .badge {
        font-size: 0.85em !important;
        padding: 5px 9px !important;
    }

    #addon-block { margin-top: 12px !important; }
}

/* Category Sidebar Styling */
.category-sidebar {
    border: 1px solid #e2e8f0;
}

.category-sidebar .category-sidebar {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    padding: 0 !important;
}

.category-sidebar .fw-bold {
    font-size: 1.1em !important;
    color: #007BFF !important;
    border-bottom: 2px solid #e3e8f0;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

 /* Override category sidebar title size */
 .category-sidebar-title {
     font-size: 0.91em !important;
     line-height: 1.2;
 }
 
 /* Keep category links compact */
 .category-sidebar .category-link {
     font-size: 0.78em !important;
     line-height: 1.25;
 }

 .category-sidebar .category-submenu .category-link {
     font-size: 0.75em !important;
     line-height: 1.25;
 }
 
/* Product Info Card Styling */
.product-info-card {
    transition: all 0.3s ease;
}

.product-info-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

/* Product Gallery Styling */
.product-gallery {
    transition: all 0.3s ease;
}

.product-gallery:hover {
    transform: scale(1.02);
}

/* Commit Cards Styling */
.commit-card {
    transition: all 0.3s ease;
    border: 1px solid #e3e8f0 !important;
}

.commit-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    border-color: #007BFF !important;
}

/* Tabs title should be larger than content */
#productTab {
    font-size: 1.25em !important;
}

#productTab .nav-link {
    font-weight: 700;
    letter-spacing: 0.2px;
}

#productTab .nav-link.active {
    border-bottom-width: 3px;
}

 #reviews-section {
     font-size: 0.85rem;
     line-height: 1.45;
 }
 
 #reviews-section h5 {
     font-size: 1rem !important;
 }
 
 #reviews-section .review-item,
 #reviews-section .reply-item {
     font-size: 0.85rem;
 }
 
 #reviews-section .review-item .fw-bold,
 #reviews-section .reply-item .fw-bold {
     font-size: 0.85rem !important;
 }
 
 #reviews-section .review-item p,
 #reviews-section .reply-item p {
     font-size: 0.85rem !important;
     line-height: 1.5 !important;
 }
 
 #reviews-section .reply-toggle-btn {
     font-size: 0.8rem !important;
     line-height: 1.2;
 }
 
 #reviews-section .reply-form input,
 #reviews-section .reply-form .form-control {
     font-size: 0.85rem !important;
 }
 
 #reviews-section .review-item [style*="diffForHumans"],
 #reviews-section .review-item span[style*="color:#999"],
 #reviews-section .review-item span[style*="color:#999;"],
 #reviews-section .review-item span[style*="color:#999"],
 #reviews-section .review-item span[style*="color:#999"],
 #reviews-section .review-item span[style*="color:#999"],
 #reviews-section .reply-item span[style*="color:#999"],
 #reviews-section .reply-item span[style*="color:#999;"] {
     font-size: 0.75rem !important;
 }
 
 #reviews-section .badge {
     font-size: 0.65rem !important;
 }
 
 #reviews-section .btn,
 #reviews-section button {
     font-size: 0.8rem;
 }
 
 #reviews-section .reviews-list {
     font-size: 0.85rem;
 }

 #reviews-section .row.g-3.mb-4 {
     padding: 16px !important;
 }
 
 /* Summary (average score) */
 #reviews-section .row.g-3.mb-4 > .col-6.col-md-2 > div:first-child {
     font-size: 1.9rem !important;
     line-height: 1.1 !important;
 }
 
 #reviews-section .row.g-3.mb-4 > .col-6.col-md-2 > div:first-child span {
     font-size: 0.85rem !important;
 }
 
 #reviews-section .row.g-3.mb-4 > .col-6.col-md-2 .bi-star,
 #reviews-section .row.g-3.mb-4 > .col-6.col-md-2 .bi-star-fill {
     font-size: 0.75rem !important;
 }
 
 #reviews-section .row.g-3.mb-4 > .col-6.col-md-2 div[style*="lượt đánh giá"] {
     font-size: 0.75rem !important;
 }
 
 #reviews-section .row.g-3.mb-4 > .col-6.col-md-2 .btn {
     font-size: 0.75rem !important;
     padding: 5px 12px !important;
 }
 
 /* Star bars (5*..1*) */
 #reviews-section .row.g-3.mb-4 > .col-6.col-md-5 span {
     font-size: 0.75rem !important;
 }
 
 /* Experience block */
 #reviews-section .row.g-3.mb-4 > .col-12.col-md-5 .fw-bold {
     font-size: 0.85rem !important;
 }
 
 #reviews-section .row.g-3.mb-4 > .col-12.col-md-5 span {
     font-size: 0.75rem !important;
 }
 
 #reviews-section .row.g-3.mb-4 > .col-12.col-md-5 .bi-star,
 #reviews-section .row.g-3.mb-4 > .col-12.col-md-5 .bi-star-fill {
     font-size: 0.7rem !important;
 }
 
 /* Filters (Lọc theo + pills) */
 #reviews-section .mb-3.d-flex span {
     font-size: 0.8rem !important;
 }
 
 #reviews-section .mb-3.d-flex .btn {
     font-size: 0.75rem !important;
     padding: 4px 10px !important;
 }

/* Mobile/tablet responsive cho sidebar */
@media (max-width: 991.98px) {
    /* Ẩn sidebar trên mobile */
    .col-12.col-md-3 {
        display: none;
    }
    
    /* Content chiếm toàn bộ width trên mobile */
    .col-12.col-md-9 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .category-sidebar {
        position: static !important;
        margin-bottom: 20px;
        border-radius: 8px !important;
        padding: 16px !important;
    }
    
    .product-info-card {
        margin-top: 20px;
        padding: 16px !important;
    }
    
    /* Mobile Product Title */
    .product-title-mobile {
        font-size: 1.15em !important;
        line-height: 1.3 !important;
        margin-bottom: 12px !important;
    }
    
    /* Mobile Product Gallery */
    .product-gallery {
        margin-bottom: 16px;
    }
    
    .product-gallery .main-image-container {
        min-height: 280px !important;
        padding: 16px !important;
        border-radius: 16px !important;
    }
    
    .product-gallery .main-image-container img {
        max-height: 240px !important;
    }
    
    /* Mobile Thumbnails */
    .thumbnail-container {
        gap: 6px !important;
    }
    
    .thumbnail-wrapper img {
        width: 60px !important;
        height: 60px !important;
    }
    
    /* Mobile Commit Cards */
    .commit-card {
        min-height: 100px !important;
        padding: 12px !important;
    }
    
    .commit-card .rounded-circle {
        width: 40px !important;
        height: 40px !important;
    }
    
    .commit-card i {
        font-size: 1.8em !important;
    }
    
    .commit-card div {
        font-size: 0.9em !important;
    }
    
    /* Mobile Product Info */
    .product-info-card .price-section {
        margin-bottom: 16px !important;
    }
    
    .product-info-card .price-section div:first-child {
        font-size: 0.9em !important;
    }
    
    /* Chỉ áp dụng cho giá tiền số, không áp dụng cho "Liên hệ" */
    .product-info-card .price-section > div:last-child > div:not(.product-contact-price):not(a) {
        font-size: 2em !important;
    }
    
    /* Mobile Buttons */
    .btn-mobile {
        padding: 12px 16px !important;
        font-size: 1em !important;
        border-radius: 12px !important;
    }
    
    /* Mobile Addon Section */
    .addon-section-mobile {
        margin-top: 16px !important;
    }
    
    .addon-item-mobile {
        padding: 8px !important;
        min-height: 60px !important;
    }
    
    .addon-item-mobile img {
        width: 36px !important;
        height: 36px !important;
    }
    
    /* Mobile Related Products */
    .related-products-mobile {
        margin-top: 20px !important;
    }
    
    .related-products-mobile h4 {
        font-size: 0.9rem !important;
        margin-bottom: 12px !important;
    }
    
    .related-product-card-mobile {
        min-height: 220px !important;
        border-radius: 8px !important;
    }
    
    .related-product-card-mobile img {
        max-height: 90px !important;
        padding: 6px !important;
    }
    
    .related-product-card-mobile .card-body {
        padding: 8px !important;
    }
    
    .related-product-card-mobile .card-title {
        font-size: 0.75rem !important;
        min-height: 24px !important;
        margin-bottom: 4px !important;
    }
    
    .related-product-card-mobile .product-card-desc {
        font-size: 0.65rem !important;
        min-height: 12px !important;
        margin-bottom: 4px !important;
    }
    
    .related-product-card-mobile .product-card-price span {
        font-size: 0.75rem !important;
    }
    
    .related-product-card-mobile .btn {
        font-size: 0.7rem !important;
        padding: 6px 8px !important;
        min-height: 32px !important;
    }
    
    .related-product-card-mobile .mb-2 > div {
        font-size: 0.6rem !important;
        padding: 2px 4px !important;
    }
    
    /* Ẩn hover effects trên mobile */
    .product-hover-tooltip {
        display: none !important;
    }
    
    .product-hover-container:hover {
        transform: none !important;
    }
    
    .product-hover-container:hover .product-hover-tooltip {
        opacity: 0 !important;
        visibility: hidden !important;
    }
    
    /* Tối ưu touch targets */
    .btn, .btn-mobile {
        min-height: 44px !important;
    }
    
    input[type="checkbox"], input[type="radio"] {
        min-width: 20px !important;
        min-height: 20px !important;
    }
    
    /* Tối ưu scrolling */
    .container-fluid {
        overflow-x: hidden;
    }
    
    /* Tối ưu mobile dropdown */
    .dropdown-menu {
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        border: 1px solid #e3e8f0;
    }
    
    .dropdown-item.active {
        background-color: #007BFF;
        color: white;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .dropdown-item.active:hover {
        background-color: #0056b3;
    }
    
    /* Tối ưu product title trên mobile */
    .product-title-mobile {
        font-size: 1.3em !important;
        line-height: 1.2 !important;
        margin-bottom: 12px !important;
    }
    
    /* Tối ưu product gallery trên mobile */
    .product-gallery .main-image-container {
        min-height: 250px !important;
        padding: 12px !important;
        border-radius: 12px !important;
    }
    
    .product-gallery .main-image-container img {
        max-height: 200px !important;
    }
    
    /* Tối ưu thumbnails trên mobile */
    .thumbnail-container {
        gap: 4px !important;
        margin-top: 12px !important;
    }
    
    .thumbnail-wrapper img {
        width: 50px !important;
        height: 50px !important;
    }
    
    /* Tối ưu commit cards trên mobile */
    .commit-card {
        min-height: 80px !important;
        padding: 10px !important;
        margin-bottom: 12px !important;
    }
    
    .commit-card .rounded-circle {
        width: 35px !important;
        height: 35px !important;
    }
    
    .commit-card i {
        font-size: 1.5em !important;
    }
    
    .commit-card div {
        font-size: 0.85em !important;
    }
    
    /* Tối ưu addon section trên mobile */
    .addon-section-mobile {
        margin-top: 12px !important;
    }
    
    .addon-item-mobile {
        padding: 6px !important;
        min-height: 50px !important;
    }
    
    .addon-item-mobile img {
        width: 30px !important;
        height: 30px !important;
    }
    
    /* Tối ưu tabs trên mobile */
    .nav-tabs {
        font-size: 1.1em !important;
    }
    
    .nav-tabs .nav-link {
        padding: 8px 12px !important;
        font-size: 0.9em !important;
    }
    
    /* Tối ưu related products trên mobile nhỏ */
    .related-products-mobile {
        margin-top: 16px !important;
    }
    
    .related-products-mobile h4 {
        font-size: 0.85rem !important;
        margin-bottom: 10px !important;
    }
    
    .related-product-card-mobile {
        min-height: 200px !important;
    }
    
    .related-product-card-mobile img {
        max-height: 80px !important;
    }
    
    .related-product-card-mobile .card-title {
        font-size: 0.7rem !important;
        min-height: 20px !important;
    }
    
    .related-product-card-mobile .btn {
        font-size: 0.65rem !important;
        padding: 4px 6px !important;
        min-height: 28px !important;
    }
    
    /* Kích thước chữ "Liên hệ" trên mobile - Chỉ target class product-contact-price */
    .product-contact-price,
    .product-info-card .price-section .product-contact-price,
    .product-info-card .price-section a .product-contact-price,
    .product-info-card .price-section > div > a > .product-contact-price,
    .price-section .product-contact-price,
    .price-section a .product-contact-price,
    .price-section > div > a > .product-contact-price,
    div.product-contact-price[style*="font-size:2.7em"],
    div.product-contact-price[style*="font-size:2em"] {
        font-size: 1.5em !important;
        font-weight: 600 !important;
        line-height: 1.2 !important;
        letter-spacing: 0 !important;
    }
}

/* CSS riêng với độ ưu tiên cao nhất cho "Liên hệ" trên mobile - Đặt ngoài media query để đảm bảo override */
@media (max-width: 767.98px) {
    /* Force override inline style cho "Liên hệ" - Chỉ target class product-contact-price */
    .product-contact-price,
    .product-contact-price[style*="font-size"],
    .product-info-card .price-section .product-contact-price,
    .product-info-card .price-section a .product-contact-price,
    .product-info-card .price-section > div > a > .product-contact-price,
    .price-section .product-contact-price,
    .price-section a .product-contact-price,
    .price-section > div > a > .product-contact-price {
        font-size: 1.5em !important;
        font-weight: 600 !important;
        line-height: 1.2 !important;
        letter-spacing: 0 !important;
    }
}

/* Related products title size (desktop/all sizes). Override global .card-title !important in custom-fonts.css */
.related-products-mobile .related-product-card-mobile .card-title {
    font-size: 0.8rem !important;
    line-height: 1.25 !important;
}

/* Mobile/tablet responsive cho sidebar */
@media (max-width: 991.98px) {
    .category-sidebar {
        position: static !important;
        margin-bottom: 20px;
        border-radius: 8px !important;
    }
    
    .category-sidebar .fw-bold {
        font-size: 1em !important;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }
}

.product-hover-container {
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
}

.product-hover-container:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,123,255,0.15) !important;
}

.product-hover-container:hover .product-hover-tooltip {
    opacity: 1 !important;
    visibility: visible !important;
    transform: translateY(0) !important;
}

.product-hover-tooltip {
    transform: translateY(20px);
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px rgba(0,123,255,0.15);
    position: relative;
}

.product-hover-tooltip::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg, #007bff, #00d4ff, #007bff);
    border-radius: 0 0 1.5rem 1.5rem;
    z-index: -1;
    opacity: 0.6;
    animation: borderGlow 2s ease-in-out infinite alternate;
}

@keyframes borderGlow {
    0% { opacity: 0.4; }
    100% { opacity: 0.8; }
}

.product-hover-tooltip .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,123,255,0.4) !important;
}

.product-hover-tooltip .btn-outline-primary:hover {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border-color: #007bff;
    color: white;
}

.pd-page {
    --pd-surface: #ffffff;
    --pd-border: rgba(31,45,61,0.10);
    --pd-shadow: 0 10px 26px rgba(2,6,23,0.06);
    background: #ffffff;
}

.pd-breadcrumb {
    font-size: 0.8rem;
    color: #6c757d;
}

.pd-breadcrumb .breadcrumb {
    flex-wrap: nowrap;
    overflow: hidden;
}

.pd-breadcrumb .breadcrumb-item {
    white-space: nowrap;
}

.pd-breadcrumb .breadcrumb-item a,
.pd-breadcrumb .breadcrumb-item.active {
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.pd-breadcrumb .breadcrumb-item:last-child {
    min-width: 0;
    flex: 1 1 auto;
}

.pd-breadcrumb .breadcrumb-item:last-child .breadcrumb-item.active,
.pd-breadcrumb .breadcrumb-item:last-child {
    min-width: 0;
}

.pd-breadcrumb .breadcrumb-item:last-child a,
.pd-breadcrumb .breadcrumb-item:last-child span,
.pd-breadcrumb .breadcrumb-item:last-child {
    max-width: 100%;
}

/* Buybox typography hierarchy */
.pd-buybox-title {
    font-size: 1.35rem !important;
}

.pd-price-label {
    font-size: 0.86rem !important;
    letter-spacing: 0.2px;
}

.pd-price-main {
    font-size: 2.05em !important;
    letter-spacing: -1.5px !important;
}

.pd-price-row {
    display: flex;
    align-items: baseline;
    gap: 18px;
}

.pd-price-vat {
    font-size: 0.85rem;
    color: #94a3b8;
    font-weight: 500;
    white-space: nowrap;
}

.pd-price-old {
    font-size: 1.05em !important;
}

/* Badges + qty: secondary level */
.pd-buybox .badge {
    font-size: 0.92rem !important;
    font-weight: 700;
}

.pd-buybox label[for="qty"] {
    font-size: 0.95rem !important;
}

.pd-buybox #qty {
    font-size: 0.98rem !important;
}

/* CTA: prominent but not overpowering */
.pd-buybox .btn-mobile {
    font-size: 0.98em !important;
    padding: 11px 0 !important;
    letter-spacing: 0.25px !important;
}

/* Buybox spacing: make mb-3 tighter (higher specificity to ensure it applies) */
.product-info-card.pd-buybox .mb-3 {
    margin-bottom: 0.5rem !important;
}



/* Quick contact text levels */
.pd-quick-contact .pd-qc-title {
    font-size: 0.7rem !important;
    letter-spacing: 0.3px !important;
}

.pd-quick-contact .pd-qc-number {
    font-size: 0.96rem !important;
}

/* Promo banner under buybox */
.pd-buybox-promo {
    margin-top: 10px;
}

.pd-buybox-promo img {
    width: 100%;
    height: auto;
    display: block;
    border-radius: 14px;
    border: 1px solid rgba(15, 23, 42, 0.10);
}

@media (max-width: 576px) {
    .pd-breadcrumb {
        font-size: 0.76rem;
    }

    .pd-buybox-title {
        font-size: 1.15rem !important;
    }

    .pd-price-main {
        font-size: 1.85em !important;
    }

    .pd-buybox .btn-mobile {
        font-size: 0.96em !important;
        padding: 10px 0 !important;
    }

    .pd-quick-contact .pd-qc-number {
        font-size: 0.92rem !important;
    }
}

.pd-breadcrumb a {
    color: inherit;
    text-decoration: none;
}

.pd-breadcrumb a:hover {
    color: var(--brand-primary);
}

.pd-card {
    background: var(--pd-surface);
    border-radius: 16px;
    box-shadow: var(--pd-shadow);
}

.pd-buybox {
    position: sticky;
    top: 20px;
    align-self: flex-start;
}

.pd-mobile-header {
    position: sticky;
    top: 0;
    z-index: 1040;
    background: #fff;
    border-bottom: 1px solid rgba(31,45,61,0.10);
}

.pd-mobile-header .pd-mh-btn {
    width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

@media (max-width: 767.98px) {
    #mobileCategoryDropdown {
        font-size: 0.95rem;
        padding: 10px 14px;
        white-space: normal;
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: break-word;
        line-height: 1.25;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.5rem;
        padding-right: 2.25rem;
    }

    #mobileCategoryDropdown::after {
        margin-left: auto;
        flex: 0 0 auto;
    }

    #mobileCategoryDropdown i {
        flex: 0 0 auto;
    }

    .addon-section-mobile .addon-scroll {
        display: flex;
        flex-wrap: nowrap;
        gap: 10px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scroll-snap-type: x mandatory;
        padding-bottom: 6px;
    }

    .addon-section-mobile .addon-scroll::-webkit-scrollbar {
        display: none;
    }

    .addon-section-mobile .addon-scroll-item {
        flex: 0 0 auto;
        width: 160px;
        scroll-snap-align: start;
    }

    .addon-section-mobile .addon-item-mobile {
        min-height: 0 !important;
        padding: 10px !important;
    }

    .addon-section-mobile .addon-item-mobile .addon-thumb {
        width: 44px !important;
        height: 44px !important;
        border-radius: 10px !important;
    }

    .addon-section-mobile .addon-item-mobile .addon-price {
        font-size: 0.72rem !important;
    }
}

@media (max-width: 991.98px) {
    .pd-buybox {
        position: static;
    }
}
</style>

@section('content')
<!-- Layout 2 cột với sidebar bên trái -->
<div class="d-md-none pd-mobile-header">
    <div class="container py-2">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <button type="button" class="btn btn-outline-secondary pd-mh-btn" aria-label="Quay lại" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('home') }}'; }">
                <i class="bi bi-arrow-left"></i>
            </button>

            <div class="d-flex align-items-center gap-2">
                <a href="/cart" class="btn btn-outline-secondary pd-mh-btn" aria-label="Giỏ hàng">
                    <i class="bi bi-cart3"></i>
                </a>
                <button type="button" class="btn btn-outline-primary pd-mh-btn" data-bs-toggle="offcanvas" data-bs-target="#pdMobileCategoryOffcanvas" aria-controls="pdMobileCategoryOffcanvas" aria-label="Danh mục">
                    <i class="bi bi-grid-3x3-gap-fill"></i>
                </button>
            </div>
        </div>

        <div class="mt-2" style="min-width:0;">
            <div class="small" style="color:#6c757d; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                {{ $product->category->name ?? 'Không phân loại' }}
            </div>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="pdMobileCategoryOffcanvas" aria-labelledby="pdMobileCategoryOffcanvasLabel">
    <div class="offcanvas-header" style="background:#e11d2e; padding-top:12px; padding-bottom:12px;">
        <div class="d-flex align-items-center gap-2" style="min-width:0;">
            <img src="/logovigilance.jpg" alt="Logo" style="height:22px; max-height:22px; display:block;">
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body pt-3">
        <div class="d-flex align-items-center gap-2 mb-2" style="color:#e11d2e; font-weight:800; letter-spacing:0.5px;">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            <div>DANH MỤC SẢN PHẨM</div>
        </div>
        @include('components.sidebar', ['categories' => $categories, 'currentCategory' => $product->category ?? null, 'offcanvas' => true])
    </div>
</div>

<div class="container py-4 pd-page">
    <div class="row g-4">
        <!-- Cột trái: Sidebar danh mục -->
        <div class="col-12 col-md-3">
            <div class="category-sidebar" style="background: #fff; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 24px; position: sticky; top: 20px; border: 1px solid #e2e8f0;">
                @include('components.sidebar', ['categories' => $categories])
            </div>
        </div>
        
        <!-- Cột phải: Nội dung sản phẩm -->
        <div class="col-12 col-md-9">
            <nav class="pd-breadcrumb mb-2 d-md-none" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider: '›';">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    @if(!empty($product->category))
                        <li class="breadcrumb-item"><a href="{{ route('category.show', $product->category->slug) }}">{{ $product->category->name }}</a></li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>

            <!-- Mobile Category Dropdown -->
            <div class="d-none mb-3">
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle w-100" type="button" id="mobileCategoryDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="text-align: left;">
                        <i class="bi bi-list me-2"></i>{{ $product->category->name ?? 'Không phân loại' }}
                    </button>
                    <ul class="dropdown-menu w-100" aria-labelledby="mobileCategoryDropdown" style="max-height: 300px; overflow-y: auto;">
                        @foreach($categories as $cat)
                            <li>
                                <a class="dropdown-item {{ $cat->id == ($product->category->id ?? 0) ? 'active' : '' }}" href="{{ route('category.show', $cat->slug) }}" style="padding: 10px 16px; font-size: 0.95em;">
                                    <i class="bi bi-folder me-2"></i>{{ $cat->name }}
                                </a>
                            </li>
                            @if($cat->children && $cat->children->count() > 0)
                                @foreach($cat->children as $child)
                                    <li>
                                        <a class="dropdown-item {{ $child->id == ($product->category->id ?? 0) ? 'active' : '' }}" href="{{ route('category.show', $child->slug) }}" style="padding: 10px 16px 10px 32px; font-size: 0.9em; color: #666;">
                                            <i class="bi bi-folder-fill me-2"></i>{{ $child->name }}
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
            
            <!-- Product Title -->
            <div class="mb-4 d-md-none">
                <h1 class="fw-bold mb-0 product-title-mobile"
                    style="color:#222; font-size:1.8em !important; font-weight:900; line-height:1.2; letter-spacing:-0.5px; word-break:break-word; text-align:left; white-space:normal;">
                    {{ $product->name }}
                </h1>
            </div>
            
            <!-- Product Main Content -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    {{-- Product Image Gallery --}}
                    <div class="product-gallery">
                {{-- Main Image Container --}}
                <div class="main-image-container pd-card" style="display:flex; align-items:center; justify-content:center; min-height:400px; margin-bottom:16px; padding:20px; overflow:hidden; position:relative; border-radius:24px;">

                    
                    {{-- Main Image --}}
                    <img id="mainProductImage" src="{{ asset('images/products/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid rounded" style="max-width:100%; max-height:320px; width:auto; height:auto; object-fit:contain; cursor:zoom-in;">
                </div>

                {{-- Thumbnail Images --}}
                @if($product->images && $product->images->count() > 0)
                    <div class="thumbnail-container" style="display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">
                        {{-- Thumbnail cho ảnh chính --}}
                        <div class="thumbnail-wrapper" style="position:relative; cursor:pointer;">
                            <img src="{{ asset('images/products/' . $product->image) }}" 
                                 alt="{{ $product->name }}" 
                                 class="product-thumbnail-img" 
                                 data-index="0"
                                 style="width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid #007bff; transition:all 0.3s ease;"
                                 onclick="changeMainImage(0)">
                            <div class="active-indicator" style="position:absolute; top:-4px; right:-4px; width:20px; height:20px; background:#007bff; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-size:0.7em; font-weight:bold;">✓</div>
                        </div>
                        
                        {{-- Thumbnail cho ảnh bổ sung --}}
                        @foreach($product->images as $index => $image)
                            <div class="thumbnail-wrapper" style="position:relative; cursor:pointer;">
                                <img src="{{ asset('images/products/' . $image->image_path) }}" 
                                     alt="{{ $image->alt_text ?? $product->name }}" 
                                     class="product-thumbnail-img" 
                                     data-index="{{ $index + 1 }}"
                                     style="width:80px; height:80px; object-fit:cover; border-radius:8px; border:2px solid #e3e8f0; transition:all 0.3s ease;"
                                     onclick="changeMainImage({{ $index + 1 }})">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
          
            <div class="product-commitments mt-4 mb-4">
                <div class="fw-bold mb-3" style="font-size:1.1rem; color:#222;">Cam kết sản phẩm</div>
                <div class="pd-card p-3" style="background:#fff; border-radius:16px;">
                    <div class="d-grid gap-2">
                        <div class="d-flex align-items-start gap-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width:36px; height:36px; background:#fef2f2; border:1px solid rgba(227,0,25,0.12);">
                                <i class="bi bi-patch-check-fill" style="font-size:1rem; color:#e30019;"></i>
                            </span>
                            <div style="font-size:0.9rem; color:#334155; line-height:1.6;">Máy mới 100%, chính hãng. Vigilance hiện là đại lý bán lẻ ủy quyền chính hãng.</div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width:36px; height:36px; background:#fef2f2; border:1px solid rgba(227,0,25,0.12);">
                                <i class="bi bi-arrow-repeat" style="font-size:1rem; color:#e30019;"></i>
                            </span>
                            <div style="font-size:0.9rem; color:#334155; line-height:1.6;">1 Đổi 1 trong 30 ngày nếu có lỗi phần cứng nhà sản xuất. Bảo hành 12 tháng tại trung tâm bảo hành chính hãng.</div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width:36px; height:36px; background:#fef2f2; border:1px solid rgba(227,0,25,0.12);">
                                <i class="bi bi-box-seam" style="font-size:1rem; color:#e30019;"></i>
                            </span>
                            <div style="font-size:0.9rem; color:#334155; line-height:1.6;">Đầy đủ phụ kiện: Hộp, Sách hướng dẫn, Cáp sạc, Phụ kiện theo tiêu chuẩn nhà sản xuất.</div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <span class="d-flex align-items-center justify-content-center rounded-2 flex-shrink-0" style="width:36px; height:36px; background:#fef2f2; border:1px solid rgba(227,0,25,0.12);">
                                <i class="bi bi-receipt" style="font-size:1rem; color:#e30019;"></i>
                            </span>
                            <div style="font-size:0.9rem; color:#334155; line-height:1.6;">Giá sản phẩm <b>chưa bao gồm thuế VAT</b>, có hỗ trợ xuất hóa đơn đầy đủ cho doanh nghiệp.</div>
                        </div>
                    </div>
                </div>
            </div>
                </div>
                <div class="col-md-6">
                    <!-- Product Info Card -->
                    <div class="product-info-card pd-card pd-buybox">
                        <div class="mb-3 d-none d-md-block">
                            <nav class="pd-breadcrumb mb-2" aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider: '›';">
                                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                                    @if(!empty($product->category))
                                        <li class="breadcrumb-item"><a href="{{ route('category.show', $product->category->slug) }}">{{ $product->category->name }}</a></li>
                                    @endif
                                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                                </ol>
                            </nav>

                            <h1 class="fw-bold mb-0 pd-buybox-title" style="color:#0f172a; font-size:1.35rem !important; font-weight:900; line-height:1.2; letter-spacing:-0.4px; word-break:break-word;">
                                {{ $product->name }}
                            </h1>
                        </div>
                        <!-- Price Section -->
                        <div class="mb-4 price-section">
                            <div class="pd-price-label" style="font-size:0.9rem; color:#64748b; font-weight:600; margin-bottom:8px;">Giá sản phẩm</div>
                            @if($product->has_discount)
                                <span class="badge bg-danger" style="position:absolute; top:18px; right:24px; font-size:1.1em; padding:7px 18px; border-radius:1em; z-index:2;">Giảm {{ $product->discount_percent }}%</span>
                                <div class="pd-price-row">
                                    <div class="pd-price-main" style="font-size:1.9em; font-weight:900; color:#e30019; line-height:1; letter-spacing:-2px;">{{ number_format($product->final_price, 0, ',', '.') }}đ</div>
                                    <span class="pd-price-vat">(Chưa bao gồm thuế VAT)</span>
                                    <div class="pd-price-old" style="font-size:1.15em; color:#b0b0b0; text-decoration:line-through; font-weight:600;">{{ number_format($product->price, 0, ',', '.') }}đ</div>
                                </div>
                            @else
                                <div class="pd-price-row">
                                    @if($product->price > 0)
                                        <div class="pd-price-main" style="font-size:2.2em; font-weight:900; color:#d32f2f; line-height:1; letter-spacing:-2px;">{{ number_format($product->price, 0, ',', '.') }}đ</div>
                                        <span class="pd-price-vat">(Chưa bao gồm thuế VAT)</span>
                                    @else
                                        <a href="https://zalo.me/0982751039" target="_blank" style="text-decoration:none;">
                                            <div class="product-contact-price pd-price-main" style="font-size:2.2em; font-weight:900; color:#d32f2f; line-height:1; letter-spacing:-2px; cursor:pointer; transition:color 0.3s ease;" onmouseover="this.style.color='#b71c1c'" onmouseout="this.style.color='#d32f2f'">Liên hệ</div>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
            {{-- Chọn màu sắc sản phẩm - Design hiện đại --}}
            @if($product->colors && $product->colors->count())
            <div class="mb-4">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                    <label class="form-label fw-bold" style="font-size:1.8em; margin:0;">Màu sắc:</label>
                    <span class="badge bg-warning" style="font-size:0.9em;">Chọn màu yêu thích</span>
                </div>
                <div class="d-flex flex-wrap gap-4" id="color-options">
                    @foreach($product->colors as $color)
                        <div class="color-option-wrapper" style="position:relative;">
                            <button type="button"
                                class="btn color-option-btn"
                                data-price="{{ $color->price ?? $product->final_price }}"
                                data-quantity="{{ $color->quantity }}"
                                data-color="{{ $color->color_code }}"
                                data-color-name="{{ $color->color_name }}"
                                data-id="{{ $color->id }}"
                                style="border:3px solid {{ $color->quantity > 0 ? '#e3e8f0' : '#ffcdd2' }}; background:linear-gradient(145deg, #ffffff 0%, #f8fafc 100%); min-width:180px; min-height:80px; position:relative; opacity:{{ $color->quantity > 0 ? '1' : '0.6' }}; padding:16px 20px; font-size:1.1em; display:flex; align-items:center; gap:16px; border-radius:16px; box-shadow:0 4px 12px rgba(0,0,0,0.08); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); overflow:hidden;"
                                @if($color->quantity == 0) disabled @endif
                            >
                                {{-- Color swatch với gradient và shine effect --}}
                                <div style="position:relative; width:48px; height:48px; border-radius:50%; background:{{ $color->color_code }}; border:3px solid #fff; box-shadow:0 2px 8px rgba(0,0,0,0.15); display:flex; align-items:center; justify-content:center; overflow:hidden;">
                                    <div style="position:absolute; top:-50%; left:-50%; width:200%; height:200%; background:linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%); transform:rotate(45deg); transition:transform 0.3s;"></div>
                                </div>
                                
                                <div style="flex:1; text-align:left;">
                                    <div style="font-weight:700; color:#2d3748; margin-bottom:4px;">{{ $color->color_name }}</div>
                                    <div style="font-size:1.05em; font-weight:600; color:#e30019;">@if($color->price){{ number_format($color->price, 0, ',', '.') }}đ @else {{ number_format($product->final_price, 0, ',', '.') }}đ @endif</div>
                                </div>
                                
                                {{-- Stock indicator --}}
                                @if($color->quantity > 0)
                                    <div style="position:absolute; top:8px; right:8px; background:#007BFF; color:#fff; font-size:0.75em; padding:2px 6px; border-radius:8px; font-weight:600; z-index:2;">Còn hàng</div>
                                @else
                                    <div style="position:absolute; top:8px; right:8px; background:#ef4444; color:#fff; font-size:0.75em; padding:2px 6px; border-radius:8px; font-weight:600; z-index:2;">Hết hàng</div>
                                @endif
                                @if($loop->first)
                                    <div style="position:absolute; top:8px; left:8px; background:#f59e0b; color:#fff; font-size:0.7em; padding:2px 6px; border-radius:6px; font-weight:600; z-index:2;">Phổ biến</div>
                @endif
                                
                                {{-- Selection indicator --}}
                                <div class="selection-indicator" style="position:absolute; top:-2px; right:-2px; width:24px; height:24px; background:var(--brand-primary); border-radius:50%; display:none; align-items:center; justify-content:center; color:#fff; font-size:0.8em; font-weight:bold; box-shadow:0 2px 8px rgba(227,0,25,0.3);">
                                    ✓
                                </div>
                            </button>
                        </div>
                    @endforeach
                </div>
                
                {{-- Color preview section --}}
                <div id="color-preview" style="margin-top:16px; padding:16px; background:linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%); border-radius:12px; border:1px solid #e2e8f0; display:none;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div id="preview-color-swatch" style="width:32px; height:32px; border-radius:50%; border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.1);"></div>
                        <div>
                            <div id="preview-color-name" style="font-weight:600; color:#2d3748;"></div>
                            <div id="preview-color-price" style="font-size:0.9em; color:#718096;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const colorBtns = document.querySelectorAll('.color-option-btn');
                const priceBlock = document.querySelector('.price-section');
                // Make addon card clickable to open product
                document.querySelectorAll('.addon-card').forEach(function(card){
                    card.addEventListener('click', function(e){
                        // avoid navigate when clicking checkbox
                        if(e.target && (e.target.tagName === 'INPUT' || e.target.classList.contains('form-check-input'))) return;
                        const url = this.getAttribute('data-url');
                        if(url && url !== '#') {
                            window.location.href = url;
                        }
                    });
                });
                const colorPreview = document.getElementById('color-preview');
                const previewColorSwatch = document.getElementById('preview-color-swatch');
                const previewColorName = document.getElementById('preview-color-name');
                const previewColorPrice = document.getElementById('preview-color-price');
                const addToCartForm = document.getElementById('addToCartForm');
                
                let selectedBtn = null;
                const originalPriceBlock = priceBlock ? priceBlock.innerHTML : '';
                
                colorBtns.forEach(btn => {
                    // Hover effects
                    btn.addEventListener('mouseenter', function() {
                        if (!this.disabled) {
                            this.style.transform = 'translateY(-4px) scale(1.02)';
                            this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                            this.style.borderColor = 'var(--brand-primary)';
                            
                            // Show color preview
                            const colorCode = this.dataset.color;
                            const colorName = this.dataset.colorName;
                            const price = this.dataset.price;
                            
                            previewColorSwatch.style.background = colorCode;
                            previewColorName.textContent = colorName;
                            previewColorPrice.textContent = Number(price).toLocaleString() + 'đ';
                            colorPreview.style.display = 'block';
                        }
                    });
                    
                    btn.addEventListener('mouseleave', function() {
                        if (!this.classList.contains('selected')) {
                            this.style.transform = 'translateY(0) scale(1)';
                            this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
                            this.style.borderColor = this.dataset.quantity > 0 ? '#e3e8f0' : '#ffcdd2';
                        }
                    });
                    
                    // Click handler
                    btn.addEventListener('click', function() {
                        if (this.disabled) return;
                        
                        // Remove previous selection
                        colorBtns.forEach(b => {
                            b.classList.remove('selected');
                            b.style.transform = 'translateY(0) scale(1)';
                            b.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
                            b.style.borderColor = b.dataset.quantity > 0 ? '#e3e8f0' : '#ffcdd2';
                            b.querySelector('.selection-indicator').style.display = 'none';
                        });
                        
                        // Toggle selection
                        if(selectedBtn === this) {
                            selectedBtn = null;
                            if(priceBlock) {
                                const priceDisplay = priceBlock.querySelector('div[style*="display:flex"]');
                                if(priceDisplay) {
                                    // Reset về giá gốc của sản phẩm
                                    @if($product->has_discount)
                                        priceDisplay.innerHTML = `<div style="display:flex; align-items:baseline; gap:18px;">
                                            <div style="font-size:2.1em; font-weight:900; color:#e30019; line-height:1; letter-spacing:-2px;">{{ number_format($product->final_price, 0, ',', '.') }}đ</div>
                                            <div style="font-size:1.3em; color:#b0b0b0; text-decoration:line-through; font-weight:500;">{{ number_format($product->price, 0, ',', '.') }}đ</div>
                                        </div>`;
                                    @else
                                        @if($product->price > 0)
                                            priceDisplay.innerHTML = `<div style="display:flex; align-items:baseline; gap:18px;">
                                                <div style="font-size:2.7em; font-weight:900; color:#007BFF; line-height:1; letter-spacing:-2px;">{{ number_format($product->price, 0, ',', '.') }}đ</div>
                                            </div>`;
                                        @else
                                            priceDisplay.innerHTML = `<div style="display:flex; align-items:baseline; gap:18px;">
                                                <a href="https://zalo.me/0909123456" target="_blank" style="text-decoration:none;">
                                                    <div class="product-contact-price" style="font-size:2.7em; font-weight:900; color:#007BFF; line-height:1; letter-spacing:-2px; cursor:pointer; transition:color 0.3s ease;" onmouseover="this.style.color='#00B894'" onmouseout="this.style.color='#007BFF'">Liên hệ</div>
                                                </a>
                                            </div>`;
                                            // Force adjust size trên mobile
                                            setTimeout(function() {
                                                if (window.innerWidth <= 767.98) {
                                                    const contactEl = priceDisplay.querySelector('.product-contact-price');
                                                    if (contactEl) {
                                                        contactEl.style.setProperty('font-size', '1.5em', 'important');
                                                        contactEl.style.setProperty('font-weight', '600', 'important');
                                                        contactEl.style.setProperty('line-height', '1.2', 'important');
                                                        contactEl.style.setProperty('letter-spacing', '0', 'important');
                                                    }
                                                }
                                            }, 100);
                                        @endif
                                    @endif
                                }
                            }
                            colorPreview.style.display = 'none';
                            return;
                        }
                        
                        // Select new color
                        this.classList.add('selected');
                        this.style.transform = 'translateY(-2px) scale(1.01)';
                        this.style.boxShadow = '0 6px 20px rgba(0,184,148,0.3)';
                        this.style.borderColor = '#00b894';
                        this.querySelector('.selection-indicator').style.display = 'flex';
                        
                        selectedBtn = this;
                        const price = this.dataset.price;
                        const quantity = this.dataset.quantity;
                        
                        // Update price
                        if(priceBlock) {
                            const priceLabel = priceBlock.querySelector('div[style*="color:#64748b"]');
                            const priceDisplay = priceBlock.querySelector('div[style*="display:flex"]');
                            
                            if(quantity == 0) {
                                if(priceDisplay) {
                                    priceDisplay.innerHTML = '<div style="color:#e30019; font-size:2em; font-weight:900;">Liên hệ admin để đặt màu</div>';
                                }
                            } else if(price == 0) {
                                if(priceDisplay) {
                                    priceDisplay.innerHTML = '<a href="https://zalo.me/0982751039" target="_blank" style="text-decoration:none;"><div class="product-contact-price" style="font-size:2.7em; font-weight:900; color:#d32f2f; line-height:1; letter-spacing:-2px; cursor:pointer; transition:color 0.3s ease;" onmouseover="this.style.color=\'#b71c1c\'" onmouseout="this.style.color=\'#d32f2f\'">Liên hệ</div></a>';
                                    // Force adjust size trên mobile
                                    setTimeout(function() {
                                        if (window.innerWidth <= 767.98) {
                                            const contactEl = priceDisplay.querySelector('.product-contact-price');
                                            if (contactEl) {
                                                contactEl.style.setProperty('font-size', '1.5em', 'important');
                                                contactEl.style.setProperty('font-weight', '600', 'important');
                                                contactEl.style.setProperty('line-height', '1.2', 'important');
                                                contactEl.style.setProperty('letter-spacing', '0', 'important');
                                            }
                                        }
                                    }, 100);
                                }
                            } else {
                                if(priceDisplay) {
                                    priceDisplay.innerHTML = `<div style="display:flex; align-items:baseline; gap:18px;">
                                        <div style="font-size:2.7em; font-weight:900; color:#e30019; line-height:1; letter-spacing:-2px;">${Number(price).toLocaleString()}đ</div>
                                    </div>`;
                                }
                            }
                        }
                        
                        // Show color preview permanently when selected
                        const colorCode = this.dataset.color;
                        const colorName = this.dataset.colorName;
                        previewColorSwatch.style.background = colorCode;
                        previewColorName.textContent = colorName;
                        previewColorPrice.textContent = Number(price).toLocaleString() + 'đ';
                        colorPreview.style.display = 'block';
                        
                        // Add selection animation
                        this.style.animation = 'colorSelect 0.3s ease';
                        setTimeout(() => {
                            this.style.animation = '';
                        }, 300);

                        // Set giá trị color_id đúng trong form
                        if(addToCartForm) {
                            const colorInput = addToCartForm.querySelector('input[name="color_id"]');
                            if(colorInput) colorInput.value = this.dataset.id;
                            console.log('Chọn màu:', this.dataset.id);
                        }
                    });
                });
                
                // Xử lý form submit để cập nhật quantity
                if(addToCartForm) {
                    addToCartForm.addEventListener('submit', function(e) {
                        const qtyInput = document.getElementById('qty');
                        const quantityInput = document.getElementById('addToCartQty');
                        if(qtyInput && quantityInput) {
                            quantityInput.value = qtyInput.value;
                            console.log('Số lượng được cập nhật:', qtyInput.value);
                        }
                    });
                }
                
                // Add CSS animation
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes colorSelect {
                        0% { transform: scale(1); }
                        50% { transform: scale(1.05); }
                        100% { transform: scale(1.01) translateY(-2px); }
                    }
                    
                    .color-option-btn:hover .selection-indicator {
                        transform: scale(1.1);
                    }
                    
                    .color-option-btn.selected {
                        background: linear-gradient(145deg, #f0fff4 0%, #e6fffa 100%) !important;
                    }
                `;
                document.head.appendChild(style);
            });
            </script>
            @endif
            <div class="mb-3 d-flex gap-2 flex-wrap w-100">
                <form method="POST" action="{{ route('cart.add', $product->id) }}" class="d-inline-block w-100" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="quantity" value="1" id="addToCartQty">
                    <input type="hidden" name="color_id" value="">
                    <div id="selectedAddonsInputs"></div>
                    <div class="pd-buy-row mb-3">
                        <div class="pd-qty-stepper" aria-label="Số lượng">
                            <button type="button" class="pd-qty-btn" data-qty-action="decrease" aria-label="Giảm số lượng">-</button>
                            <input type="number" id="qty" name="qty" value="1" min="1" class="pd-qty-input" inputmode="numeric" aria-label="Số lượng">
                            <button type="button" class="pd-qty-btn" data-qty-action="increase" aria-label="Tăng số lượng">+</button>
                        </div>

                        <button type="submit" class="btn fw-bold w-100 btn-mobile pd-buy-btn" style="background: var(--brand-primary); color:#fff; font-size:1.02em; padding: 12px 0; transition: all 0.2s ease; border: none; cursor: pointer; font-weight: 800; letter-spacing: 0.3px; text-transform: uppercase;">
                            <span style="position: relative; z-index: 2;"><i class="bi bi-cart3 me-2"></i>Thêm vào giỏ</span>
                        </button>

                        <button type="submit" name="buy_now" value="1" class="btn fw-bold w-100 btn-mobile pd-buy-btn" style="background:#fff; color:#0f172a; font-size:1.02em; padding: 12px 0; transition: all 0.2s ease; border: 1px solid rgba(15,23,42,0.16); cursor: pointer; font-weight: 900; letter-spacing: 0.2px; text-transform: uppercase;">
                            <span style="position: relative; z-index: 2;"><i class="bi bi-lightning-charge-fill me-2"></i>Mua ngay</span>
                        </button>
                    </div>
                </form>

                <div class="pd-buybox-extras">
                    <div class="pd-card" style="background:#fff;">
                        <div class="pd-buybox-extras__section">
                            <div class="pd-quick-contact">
                                <a class="pd-qc-zalo" href="https://zalo.me/0982751039" target="_blank">
                                    <span class="pd-qc-icon" aria-hidden="true">
                                        <img src="{{ asset('icons8-zalo-48.png') }}" alt="" width="20" height="20" style="display:block;" />
                                    </span>
                                    <span class="pd-qc-text">
                                        <div class="pd-qc-title">Báo giá qua Zalo</div>
                                        <div class="pd-qc-number">0982 751 039</div>
                                    </span>
                                </a>

                                <a class="pd-qc-tech" href="https://zalo.me/0931888984" target="_blank">
                                    <span class="pd-qc-icon" aria-hidden="true">
                                        <img src="{{ asset('icons8-zalo-48.png') }}" alt="" width="20" height="20" style="display:block;" />
                                    </span>
                                    <span class="pd-qc-text">
                                        <div class="pd-qc-title">Hỗ trợ kỹ thuật Zalo</div>
                                        <div class="pd-qc-number">0931 888 984</div>
                                    </span>
                                </a>
                            </div>
                        </div>

                        @php
                            $pdPromoBanner = null;
                            $cat = $product->category ?? null;

                            $promoBannerCandidates = [];
                            if (!empty($cat)) {
                                $promoBannerCandidates[] = $cat;
                                if (!empty($cat->parent_id)) {
                                    $promoBannerCandidates[] = $cat->parent;
                                }
                            }

                            foreach ($promoBannerCandidates as $c) {
                                $promo = $c?->promo_banner;
                                if (empty($promo)) {
                                    continue;
                                }

                                $promo = ltrim((string) $promo, '/');
                                $relativePath = str_contains($promo, '/') ? $promo : ('images/banners/' . $promo);

                                if (file_exists(public_path($relativePath))) {
                                    $pdPromoBanner = $relativePath;
                                    break;
                                }
                            }
                        @endphp
                        @if(!empty($pdPromoBanner))
                            <div class="pd-buybox-extras__section">
                                <div class="pd-buybox-promo" style="margin-top:0;">
                                    <img src="{{ asset($pdPromoBanner) }}" alt="">
                                </div>
                            </div>
                        @endif

                        @php
                            $pdRelatedPromos = [
                                [
                                    'text' => 'Tặng 1 Ba lô',
                                    'code' => ' cho đơn hàng đủ điều kiện',
                                    'rest' => '(Liên hệ Zalo để được áp dụng)',
                                ],
                                [
                                    'text' => 'Miễn phí',
                                    'code' => 'vận chuyển cho đơn từ 500.000đ',
                                    'rest' => '  (Áp dụng một số sản phẩm)',
                                ],
                                [
                                    'text' => 'Đơn hàng',
                                    'code' => 'doanh nghiệp',
                                    'rest' => 'sẽ được báo giá và ưu đãi riêng qua Zalo',
                                ],
                            ];
                        @endphp

                        <div class="pd-buybox-extras__section">
                            <div class="pd-related-promos" style="margin-top:0;">
                                <div class="pd-related-promos__title">Khuyến mãi liên quan</div>
                                <ul class="pd-related-promos__list">
                                    @foreach($pdRelatedPromos as $p)
                                        <li>
                                            {{ $p['text'] }} <span class="pd-related-promos__code">{{ $p['code'] }}</span> {{ $p['rest'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($addons) && $addons->count())
        <div class="row mt-3">
            <div class="col-12">
                <div class="addon-section-mobile" id="addon-block">
                    <div class="addon-combo">
                        <div class="addon-combo__head p-3 d-flex align-items-center justify-content-between gap-2 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <span class="addon-combo__badge"><i class="bi bi-gift me-1"></i> KHUYẾN MÃI COMBO</span>
                                <div class="fw-bold" style="font-size:1rem;">Mua kèm nhận ưu đãi</div>
                            </div>
                            @if(isset($totalAddons) && $totalAddons > 6)
                                <a href="#" onclick="showAllAddonsModal(); return false;">Xem tất cả &gt;</a>
                            @endif
                        </div>

                        <div class="p-3">
                            <div class="addon-combo__list">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div class="fw-bold" style="font-size:0.95rem;">Chọn sản phẩm mua kèm</div>
                                    <div class="muted" style="font-size:0.9rem;">Tích để thêm vào giỏ cùng sản phẩm</div>
                                </div>
                                <div class="addon-scroll-wrap">
                                    <button class="addon-nav addon-nav--prev" type="button" aria-label="Trước"><i class="bi bi-chevron-left"></i></button>
                                    <button class="addon-nav addon-nav--next" type="button" aria-label="Sau"><i class="bi bi-chevron-right"></i></button>
                                    <div class="addon-scroll">
                                    @foreach($addons as $addon)
                                        <label class="addon-card" data-url="{{ isset($addon->addonProduct) ? route('product.show', $addon->addonProduct->slug) : '#' }}" data-addon-id="{{ (int) $addon->id }}" data-addon-price="{{ (int) ($addon->addon_price ?? 0) }}" data-addon-base="{{ (int) ($addon->addonProduct->price ?? 0) }}" style="cursor:pointer;">
                                            <input type="checkbox" class="form-check-input addon-checkbox" value="{{ $addon->id }}">
                                            <div class="d-flex align-items-center gap-3">
                                                <img class="addon-thumb" src="{{ asset('images/products/' . ($addon->addonProduct->image ?? '')) }}">
                                                <div style="min-width:0; flex:1 1 auto; padding-left: 4px;">
                                                    <div class="addon-title">{{ $addon->addonProduct->name ?? '' }}</div>
                                                    <div class="addon-sub">
                                                        @if($addon->discount_percent)
                                                            Giảm thêm {{ $addon->discount_percent }}%
                                                        @elseif($addon->addonProduct->price > 0 && $addon->addon_price < $addon->addonProduct->price)
                                                            @php
                                                                $discountPct = round(100 - ($addon->addon_price / $addon->addonProduct->price * 100));
                                                            @endphp
                                                            Giảm thêm {{ $discountPct }}%
                                                        @else
                                                            Ưu đãi mua kèm
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    @endforeach
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            var addonBlock = document.getElementById('addon-block');
            var addToCartForm = document.getElementById('addToCartForm');
            var selectedInputs = document.getElementById('selectedAddonsInputs');
            if (!addonBlock || !addToCartForm || !selectedInputs) return;

            var addonScroll = addonBlock.querySelector('.addon-scroll');
            var prevBtn = addonBlock.querySelector('.addon-nav--prev');
            var nextBtn = addonBlock.querySelector('.addon-nav--next');

            function scrollAddons(direction) {
                if (!addonScroll) return;
                var card = addonScroll.querySelector('.addon-card');
                var step = card ? (card.getBoundingClientRect().width + 12) : 280;
                addonScroll.scrollBy({ left: direction * step, behavior: 'smooth' });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    scrollAddons(-1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    scrollAddons(1);
                });
            }

            function syncSelectedAddonsToForm() {
                selectedInputs.innerHTML = '';
                addonBlock.querySelectorAll('.addon-checkbox').forEach(function (cb) {
                    var card = cb.closest('.addon-card');
                    if (card) {
                        card.classList.toggle('is-selected', !!cb.checked);
                    }
                    if (!cb.checked) return;
                    var id = cb.value;
                    if (!id) return;
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'addons[]';
                    input.value = id;
                    selectedInputs.appendChild(input);
                });
            }

            addonBlock.addEventListener('change', function (e) {
                if (e.target && e.target.classList && e.target.classList.contains('addon-checkbox')) {
                    syncSelectedAddonsToForm();
                }
            });

            syncSelectedAddonsToForm();
        });
        </script>
    @endif
    <!-- Đặt block tab ở đây, ngoài row trên -->
    <div class="row">
        <div class="col-12">
            {{-- Tabs chức năng chính, đặc điểm kỹ thuật, hướng dẫn sử dụng, bình luận --}}
            <ul class="nav nav-tabs mb-3" id="productTab" role="tablist" style="font-size:0.95rem;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="main-tab" data-bs-toggle="tab" data-bs-target="#main" type="button" role="tab">Các tính năng chính</button>
                </li>
                            </ul>
            <style>
                /* Mobile: biến tabs thành dạng pill cuộn ngang, dễ bấm và gọn */
                @media (max-width: 576px) {
                    #productTab {
                        font-size: 0.95em !important;
                        border-bottom: none !important;
                        display: flex !important;
                        flex-wrap: nowrap !important;
                        gap: 10px !important;
                        overflow-x: auto !important;
                        -webkit-overflow-scrolling: touch !important;
                        padding: 6px 2px 2px !important;
                        scrollbar-width: none; /* Firefox */
                    }
                    #productTab::-webkit-scrollbar { display: none; } /* Chrome/Safari */
                    #productTab .nav-item { flex: 0 0 auto !important; }
                    #productTab .nav-link {
                        white-space: nowrap !important;
                        text-align: center;
                        border-radius: 999px;
                        border: 1px solid #e5e7eb;
                        background: #fff;
                        color: #007BFF;
                        padding: 8px 14px;
                        font-weight: 700;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                    }
                    #productTab .nav-link.active {
                        background: #007BFF !important;
                        color: #fff !important;
                        border-color: #007BFF !important;
                        box-shadow: 0 4px 12px rgba(0,123,255,0.25);
                    }
                }

                /* Nút Xem thêm: thu nhỏ, cân chữ và responsive */
                #toggle-description {
                    font-size: 0.88em !important; /* Desktop to hơn một chút */
                    padding: 6px 12px !important;
                    line-height: 1.15 !important;
                    border-radius: 10px !important;
                }
                #toggle-description .bi { font-size: 0.95em; }
                @media (max-width: 576px) {
                    #toggle-description {
                        font-size: 0.68em !important; /* Mobile nhỏ hơn nữa */
                        padding: 3px 8px !important;
                    }
                }
            </style>
            <div class="tab-content p-3 bg-white rounded shadow-sm" id="productTabContent">
                <div class="tab-pane fade show active" id="main" role="tabpanel">
                   <h5 class="fw-bold mb-2" style="font-size:0.95rem;">Mô tả sản phẩm</h5>
                    <div style="position: relative;">
                        <div id="product-description" style="font-size:0.85rem; line-height:1.6; max-height: 120px; overflow: hidden; position: relative; border: 1px solid transparent;">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                        <div id="description-overlay" style="display: none; position: absolute; bottom: 0; left: 0; right: 0; height: 40px; background: linear-gradient(transparent, white); pointer-events: none; z-index: 1;"></div>
                    </div>
                    <button id="toggle-description" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="bi bi-chevron-down me-1"></i>Xem thêm
                    </button>
                </div>
                            </div>

            {{-- Thông số kỹ thuật hiển thị luôn (CellphoneS style) --}}
            <div class="bg-white rounded shadow-sm p-3 mb-4 mt-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0" style="font-size:0.95rem; color:#222;">Thông số kỹ thuật</h5>
                    <a href="#" class="text-primary" style="font-size:0.9em;">Xem tất cả ></a>
                </div>
                @php
                    $specs = $product->specifications ?? '';
                @endphp
                @if($specs)
                    @if(Str::contains($specs, '<table'))
                        {!! $specs !!}
                    @else
                        <table class="table table-bordered mb-0" style="background:#fff; border-radius:8px; overflow:hidden; font-size:0.85rem;">
                            <tbody>
                            @foreach(preg_split('/\r?\n/', $specs) as $line)
                                @php
                                    $cols = preg_split('/\t|: /', $line, 2);
                                @endphp
                                @if(trim($cols[0]))
                                    <tr>
                                        <th style="width:180px; background:#f8f9fa; color:#444; font-weight:500; padding:8px 12px; border-color:#eee;">
                                            <i class="bi bi-check-circle-fill text-primary me-1" style="font-size:0.85em;"></i>
                                            {{ trim($cols[0]) }}
                                        </th>
                                        <td style="background:#fff; padding:8px 12px; border-color:#eee; color:#333;">{{ $cols[1] ?? '' }}</td>
                                    </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                @else
                    <div class="text-muted">Chưa cập nhật thông số kỹ thuật.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="row mt-5 related-products-mobile">
        <div class="col-12">
            <h4 class="fw-bold text-center mb-4" style="letter-spacing:1px; font-size:0.95rem;">SẢN PHẨM KHÁC</h4>
            <div class="row g-3 justify-content-center">
                @forelse($relatedProducts as $item)
                    <div class="col-6 col-md-3">
                        <div class="card h-100 shadow-sm border-0 product-card position-relative product-hover-container related-product-card-mobile" data-url="{{ route('product.show', $item->slug) }}" style="min-height: 360px; display: flex; flex-direction: column;">
                            <!-- Product Hover Tooltip -->
                            <div class="product-hover-tooltip" style="position:absolute; bottom:0; left:0; width:100%; height:60%; background:linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(240,248,255,0.95) 100%); backdrop-filter:blur(10px); border-radius:0 0 1.5rem 1.5rem; padding:20px; z-index:10; opacity:0; visibility:hidden; transition:all 0.4s cubic-bezier(0.4, 0, 0.2, 1); display:flex; flex-direction:column; justify-content:center; box-shadow:0 8px 32px rgba(0,123,255,0.15); border:2px solid rgba(0,123,255,0.3); border-top:2px solid rgba(0,123,255,0.5);">
                                <div class="tooltip-header mb-3">
                                    <h6 class="fw-bold mb-2" style="color:#1a1a1a; font-size:1.1em; text-shadow:0 1px 2px rgba(0,0,0,0.1);">{{ $item->name }}</h6>
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="badge bg-primary" style="font-size:0.75em; padding:4px 8px;">{{ $item->serial_number ?? 'N/A' }}</span>
                                        <span class="badge" style="font-size:0.75em; padding:4px 8px; background-color: #007BFF; color: white;">Còn hàng</span>
                                    </div>
                                </div>
                                <div class="tooltip-content mb-3" style="flex:1;">
                                    <p style="font-size:0.9em; line-height:1.5; color:#444; margin:0; text-shadow:0 1px 1px rgba(255,255,255,0.8);">
                                        {{ Str::limit($item->description ?? 'Sản phẩm chất lượng cao với thiết kế hiện đại, phù hợp cho mọi không gian.', 120) }}
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('product.show', $item->slug) }}" class="text-decoration-none">
                                <img src="{{ asset('images/products/' . $item->image) }}" class="card-img-top rounded-3" alt="{{ $item->name }}" style="object-fit:contain; height:200px; width:100%; padding:12px; background:#fff;">
                            </a>
                            <div class="card-body pb-2 d-flex flex-column" style="flex:1 1 auto;">
                                <a href="{{ route('product.show', $item->slug) }}" class="text-decoration-none" style="color:#222;">
                                    <h6 class="card-title mb-1" style="font-size:0.8rem; min-height:28px; font-weight:600; line-height:1.25; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; color:#222;">{{ $item->name }}</h6>
                                </a>
                                <div class="product-card-desc mb-2 text-truncate" title="{{ $item->description }}" style="font-size:0.7rem; color:#666; min-height:16px;">{{ $item->description }}</div>
                                <div class="mb-2" style="min-height:40px;">
                                    <div style="background:#fff5f5; color:#E30019; font-size:0.7rem; border-radius:4px; padding:4px 8px; margin-bottom:4px;"><i class="bi bi-bag-plus-fill"></i> Giảm thêm 5% khi mua kèm Phụ kiện</div>
                                    <div style="background:#e8f5e9; color:#28a745; font-size:0.7rem; border-radius:4px; padding:4px 8px;"><i class="bi bi-cart-plus-fill"></i> Giảm thêm 10% khi mua 3 sản phẩm</div>
                                </div>
                                <div class="product-card-price mb-3" style="min-height:30px; display:flex; align-items:center;">
                                    @if($item->price > 0)
                                        <span class="fw-bold" style="color:#d32f2f; font-size:0.9rem;">{{ number_format($item->price, 0, ',', '.') }}đ</span>
                                    @else
                                        <a href="https://zalo.me/0982751039" target="_blank" style="text-decoration:none;">
                                            <span class="fw-bold" style="color:#d32f2f; font-size:0.9rem;">Liên hệ</span>
                                        </a>
                                    @endif
                                </div>
                                <a href="{{ route('product.show', $item->slug) }}" class="btn btn-sm w-100 fw-bold mt-auto" style="border-radius:0.8rem; background:var(--brand-primary); color:white; font-size:0.8rem;"><i class="bi bi-lightning-charge-fill"></i> Mua ngay</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted" style="font-size:1.2em;">Không có sản phẩm liên quan.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

<!-- Modal xem lớn ảnh (full screen + zoom) -->
<style>
  /* Đảm bảo modal và các nút điều khiển luôn ở trên cùng, cao hơn cả nút toggle tuyết (z-index: 9999) */
  #productImageModal {
    z-index: 10000 !important;
  }
  #productImageModal.modal.show {
    z-index: 10000 !important;
  }
  #productImageModal .modal-dialog {
    z-index: 10001 !important;
  }
  /* Backdrop của Bootstrap modal */
  .modal-backdrop.show {
    z-index: 9999 !important;
  }
  /* Đảm bảo các nút điều khiển trong modal luôn clickable */
  #productImageModal .position-absolute {
    pointer-events: auto;
  }
</style>
<div class="modal fade" id="productImageModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content" style="background:rgba(0,0,0,0.9); border:none; box-shadow:none;">
      <div class="modal-body p-0 d-flex justify-content-center align-items-center position-relative" style="min-height:100vh;">
        <!-- Control buttons group - Top right corner -->
        <div class="position-absolute" style="top:20px; right:20px; z-index:10010; display:flex; gap:8px; align-items:center; background:rgba(0,0,0,0.8); padding:8px 12px; border-radius:30px; box-shadow:0 2px 10px rgba(0,0,0,0.5);">
          <!-- Close button -->
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity:1; font-size:1.2em; width:36px; height:36px; z-index:10011;"></button>
        </div>
        
        <!-- Navigation buttons -->
        @if($product->images && $product->images->count() > 0)
          <button id="prevBtn" type="button" class="btn btn-light position-absolute" style="left:24px; top:50%; transform:translateY(-50%); z-index:10010; font-size:1.5em; border-radius:50%; width:50px; height:50px; display:flex; align-items:center; justify-content:center; opacity:0.9; box-shadow:0 2px 8px rgba(0,0,0,0.3);" onclick="prevImage()" title="Ảnh trước">
            <i class="bi bi-chevron-left"></i>
          </button>
          <button id="nextBtn" type="button" class="btn btn-light position-absolute" style="right:24px; top:50%; transform:translateY(-50%); z-index:10010; font-size:1.5em; border-radius:50%; width:50px; height:50px; display:flex; align-items:center; justify-content:center; opacity:0.9; box-shadow:0 2px 8px rgba(0,0,0,0.3);" onclick="nextImage()" title="Ảnh sau">
            <i class="bi bi-chevron-right"></i>
          </button>
        @endif
        
        <!-- Image counter -->
        @if($product->images && $product->images->count() > 0)
          <div class="position-absolute" style="bottom:24px; left:50%; transform:translateX(-50%); z-index:10010; background:rgba(0,0,0,0.7); color:#fff; padding:8px 16px; border-radius:20px; font-size:0.9em;">
            <span id="imageCounter">1</span> / <span>{{ $product->images->count() + 1 }}</span>
          </div>
        @endif
        
        <img id="modalProductImage" src="" alt="Ảnh sản phẩm" class="img-fluid rounded" style="max-height:98vh; max-width:98vw; box-shadow:0 4px 32px rgba(0,0,0,0.18); cursor: zoom-in; user-select: none; -webkit-user-drag: none;" />
      </div>
    </div>
  </div>
</div>


@push('scripts')
<script>
// Product Images Data
const productImages = [
    '{{ asset('images/products/' . $product->image) }}',
    @if($product->images && $product->images->count() > 0)
        @foreach($product->images as $image)
            '{{ asset('images/products/' . $image->image_path) }}'{{ !$loop->last ? ',' : '' }}
        @endforeach
    @endif
];

let currentImageIndex = 0;

// Function to change main image
function changeMainImage(index) {
    console.log('changeMainImage called with index:', index);
    console.log('productImages length:', productImages.length);
    console.log('productImages:', productImages);
    
    if (index >= 0 && index < productImages.length) {
        currentImageIndex = index;
        const mainImg = document.getElementById('mainProductImage');
        
        console.log('Updating main image to:', productImages[index]);
        
        // Update main image
        mainImg.src = productImages[index];
        
        // Update thumbnails
        updateThumbnailSelection(index);
    } else {
        console.log('Invalid index:', index);
    }
}

// Function to update thumbnail selection
function updateThumbnailSelection(activeIndex) {
    document.querySelectorAll('.thumbnail-wrapper').forEach(function (wrap) {
        wrap.classList.remove('is-active');
    });

    const activeThumbnail = document.querySelector(`[data-index="${activeIndex}"]`);
    if (activeThumbnail) {
        const wrap = activeThumbnail.closest('.thumbnail-wrapper');
        if (wrap) wrap.classList.add('is-active');
    }
}

// Function to open image modal
function openImageModal() {
    modalImageIndex = currentImageIndex;
    const modalImg = document.getElementById('modalProductImage');
    if (modalImg && productImages[modalImageIndex]) {
        modalImg.src = productImages[modalImageIndex];
        const modal = new bootstrap.Modal(document.getElementById('productImageModal'));
        modal.show();
    }
}

// Function to open modal with specific image
function openModalWithImage(index) {
    modalImageIndex = index;
    const modalImg = document.getElementById('modalProductImage');
    if (modalImg && productImages[modalImageIndex]) {
        modalImg.src = productImages[modalImageIndex];
        const modal = new bootstrap.Modal(document.getElementById('productImageModal'));
        modal.show();
    }
}

// Navigation functions for modal
function nextImage() {
    modalImageIndex = (modalImageIndex + 1) % productImages.length;
    updateModalImage(modalImageIndex);
}

function prevImage() {
    modalImageIndex = modalImageIndex === 0 ? productImages.length - 1 : modalImageIndex - 1;
    updateModalImage(modalImageIndex);
}

// Function to update modal image
function updateModalImage(index) {
    const modalImg = document.getElementById('modalProductImage');
    const imageCounter = document.getElementById('imageCounter');
    
    if (modalImg && productImages[index]) {
        modalImg.src = productImages[index];
        if (imageCounter) {
            imageCounter.textContent = index + 1;
        }
        // Reset zoom khi chuyển ảnh
        if (isZoomed) {
            isZoomed = false;
            isDragging = false;
            translateX = 0;
            translateY = 0;
            modalImg.style.transform = 'scale(1) translate(0, 0)';
            modalImg.style.cursor = 'zoom-in';
            zoom = 1;
        }
    }
}

// Variable to track current modal image index
let modalImageIndex = 0;

// Variables for zoom functionality
let zoom = 1;
let isZoomed = false;
let isDragging = false;
let startX = 0;
let startY = 0;
let translateX = 0;
let translateY = 0;

document.addEventListener('DOMContentLoaded', function() {
    var modalImg = document.getElementById('modalProductImage');
    
    // Click on main image to open modal - chỉ gắn một lần
    var mainImg = document.getElementById('mainProductImage');
    if(mainImg) {
        mainImg.style.cursor = 'zoom-in';
        mainImg.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            openImageModal();
        });
    }
    
    // Click vào ảnh để zoom in/out
    let clickStartTime = 0;
    let clickStartX = 0;
    let clickStartY = 0;
    
    modalImg.addEventListener('mousedown', function(e) {
        // Không zoom nếu click vào các nút điều khiển
        if (e.target.closest('.btn-close') || e.target.closest('#prevBtn') || e.target.closest('#nextBtn')) {
            return;
        }
        
        clickStartTime = Date.now();
        clickStartX = e.clientX;
        clickStartY = e.clientY;
        
        if (isZoomed) {
            // Nếu đã zoom, cho phép kéo ảnh
            isDragging = true;
            startX = e.clientX - translateX;
            startY = e.clientY - translateY;
            modalImg.style.cursor = 'grabbing';
            e.preventDefault();
        }
    });
    
    modalImg.addEventListener('mouseup', function(e) {
        if (isDragging) {
            isDragging = false;
            modalImg.style.cursor = 'grab';
        } else {
            // Kiểm tra nếu là click (không phải drag)
            const clickDuration = Date.now() - clickStartTime;
            const moveDistance = Math.abs(e.clientX - clickStartX) + Math.abs(e.clientY - clickStartY);
            
            if (clickDuration < 300 && moveDistance < 5) {
                // Đây là click, không phải drag
                if (!isZoomed) {
                    // Zoom in
                    zoom = 2;
                    translateX = 0;
                    translateY = 0;
                    modalImg.style.transform = 'scale(2) translate(0, 0)';
                    modalImg.style.cursor = 'grab';
                    isZoomed = true;
                } else {
                    // Zoom out
                    zoom = 1;
                    translateX = 0;
                    translateY = 0;
                    modalImg.style.transform = 'scale(1) translate(0, 0)';
                    modalImg.style.cursor = 'zoom-in';
                    isZoomed = false;
                }
            }
        }
    });
    
    modalImg.addEventListener('mousemove', function(e) {
        if (isDragging && isZoomed) {
            translateX = e.clientX - startX;
            translateY = e.clientY - startY;
            
            // Tính toán giới hạn di chuyển dựa trên kích thước ảnh
            const rect = modalImg.getBoundingClientRect();
            const imgWidth = rect.width;
            const imgHeight = rect.height;
            const maxTranslateX = imgWidth * 0.3; // Cho phép di chuyển 30% chiều rộng
            const maxTranslateY = imgHeight * 0.3; // Cho phép di chuyển 30% chiều cao
            
            translateX = Math.max(-maxTranslateX, Math.min(maxTranslateX, translateX));
            translateY = Math.max(-maxTranslateY, Math.min(maxTranslateY, translateY));
            
            modalImg.style.transform = 'scale(2) translate(' + translateX + 'px, ' + translateY + 'px)';
        } else if (isZoomed) {
            // Hiển thị cursor grab khi hover vào ảnh đã zoom
            modalImg.style.cursor = 'grab';
        }
    });
    
    modalImg.addEventListener('mouseleave', function() {
        if (isDragging) {
            isDragging = false;
            if (isZoomed) {
                modalImg.style.cursor = 'grab';
            }
        }
    });
    
    // Touch events cho mobile
    modalImg.addEventListener('touchstart', function(e) {
        if (e.target.closest('.btn-close') || e.target.closest('#prevBtn') || e.target.closest('#nextBtn')) {
            return;
        }
        
        if (isZoomed && e.touches.length === 1) {
            isDragging = true;
            startX = e.touches[0].clientX - translateX;
            startY = e.touches[0].clientY - translateY;
            e.preventDefault();
        } else if (!isZoomed && e.touches.length === 1) {
            clickStartTime = Date.now();
            clickStartX = e.touches[0].clientX;
            clickStartY = e.touches[0].clientY;
        }
    });
    
    modalImg.addEventListener('touchmove', function(e) {
        if (isDragging && isZoomed && e.touches.length === 1) {
            translateX = e.touches[0].clientX - startX;
            translateY = e.touches[0].clientY - startY;
            
            // Tính toán giới hạn di chuyển dựa trên kích thước ảnh
            const rect = modalImg.getBoundingClientRect();
            const imgWidth = rect.width;
            const imgHeight = rect.height;
            const maxTranslateX = imgWidth * 0.3;
            const maxTranslateY = imgHeight * 0.3;
            
            translateX = Math.max(-maxTranslateX, Math.min(maxTranslateX, translateX));
            translateY = Math.max(-maxTranslateY, Math.min(maxTranslateY, translateY));
            
            modalImg.style.transform = 'scale(2) translate(' + translateX + 'px, ' + translateY + 'px)';
            e.preventDefault();
        }
    });
    
    modalImg.addEventListener('touchend', function(e) {
        if (isDragging) {
            isDragging = false;
        } else if (!isZoomed) {
            const clickDuration = Date.now() - clickStartTime;
            if (clickDuration < 300) {
                // Zoom in
                zoom = 2;
                translateX = 0;
                translateY = 0;
                modalImg.style.transform = 'scale(2) translate(0, 0)';
                modalImg.style.cursor = 'grab';
                isZoomed = true;
            }
        }
    });
    
    // Reset zoom when modal is closed
    const productImageModal = document.getElementById('productImageModal');
    productImageModal.addEventListener('hidden.bs.modal', function() {
        zoom = 1;
        isZoomed = false;
        isDragging = false;
        translateX = 0;
        translateY = 0;
        modalImg.style.transform = 'scale(1) translate(0, 0)';
        modalImg.style.cursor = 'zoom-in';
        // Hiện lại nút toggle tuyết khi đóng modal
        const snowToggle = document.getElementById('snow-toggle');
        if (snowToggle) {
            snowToggle.style.display = 'flex';
        }
    });
    
    // Ẩn nút toggle tuyết khi mở modal và reset zoom state
    productImageModal.addEventListener('shown.bs.modal', function() {
        const snowToggle = document.getElementById('snow-toggle');
        if (snowToggle) {
            snowToggle.style.display = 'none';
        }
        // Reset zoom state khi mở modal mới
        zoom = 1;
        isZoomed = false;
        isDragging = false;
        translateX = 0;
        translateY = 0;
        modalImg.style.transform = 'scale(1) translate(0, 0)';
        modalImg.style.cursor = 'zoom-in';
    });

    // Keyboard navigation in modal
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('productImageModal');
        if (modal.classList.contains('show')) {
            if (e.key === 'ArrowRight') {
                nextImage();
            } else if (e.key === 'ArrowLeft') {
                prevImage();
            } else if (e.key === 'Escape') {
                bootstrap.Modal.getInstance(modal).hide();
            }
        }
    });


    // Xử lý chức năng "Xem thêm" cho mô tả sản phẩm
    function initDescriptionToggle() {
        const descriptionElement = document.getElementById('product-description');
        const toggleButton = document.getElementById('toggle-description');
        const overlayElement = document.getElementById('description-overlay');
        
        if (descriptionElement && toggleButton && overlayElement) {
            console.log('Đã tìm thấy các element cần thiết');
            
            const originalHeight = descriptionElement.scrollHeight;
            const maxHeight = 120; // 3-4 dòng (khoảng 120px)
            
            console.log('Chiều cao gốc:', originalHeight, 'px');
            console.log('Chiều cao tối đa:', maxHeight, 'px');
            
            // Kiểm tra nếu nội dung ngắn thì không cần nút "Xem thêm"
            if (originalHeight <= maxHeight) {
                console.log('Nội dung ngắn, ẩn nút và overlay');
                toggleButton.style.display = 'none';
                overlayElement.style.display = 'none';
            } else {
                console.log('Nội dung dài, hiển thị nút và overlay');
                // Hiển thị overlay gradient
                overlayElement.style.display = 'block';
                
                // Thêm event listener
                toggleButton.addEventListener('click', function() {
                    console.log('Nút được click!');
                    console.log('Trạng thái hiện tại:', descriptionElement.style.maxHeight);
                    
                    if (descriptionElement.style.maxHeight === 'none' || descriptionElement.style.maxHeight === '') {
                        // Thu gọn
                        console.log('Thu gọn nội dung');
                        descriptionElement.style.maxHeight = maxHeight + 'px';
                        descriptionElement.style.overflow = 'hidden';
                        overlayElement.style.display = 'block';
                        toggleButton.innerHTML = '<i class="bi bi-chevron-down me-1"></i>Xem thêm';
                    } else {
                        // Mở rộng
                        console.log('Mở rộng nội dung');
                        descriptionElement.style.maxHeight = 'none';
                        descriptionElement.style.overflow = 'visible';
                        overlayElement.style.display = 'none';
                        toggleButton.innerHTML = '<i class="bi bi-chevron-up me-1"></i>Thu gọn';
                    }
                });
                
                console.log('Đã thêm event listener cho nút toggle');
            }
        } else {
            console.error('Không tìm thấy các element cần thiết:');
            console.log('descriptionElement:', descriptionElement);
            console.log('toggleButton:', toggleButton);
            console.log('overlayElement:', overlayElement);
        }
    }
    
    // Khởi tạo ngay khi DOM sẵn sàng
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDescriptionToggle);
    } else {
        initDescriptionToggle();
    }
    
    // Force thay đổi kích thước chữ "Liên hệ" trên mobile
    function adjustContactTextSize() {
        if (window.innerWidth <= 767.98) {
            const contactElements = document.querySelectorAll('.product-contact-price');
            contactElements.forEach(function(el) {
                el.style.setProperty('font-size', '1.5em', 'important');
                el.style.setProperty('font-weight', '600', 'important');
                el.style.setProperty('line-height', '1.2', 'important');
                el.style.setProperty('letter-spacing', '0', 'important');
            });
        }
    }

    function initQtyStepper() {
        const qtyInput = document.getElementById('qty');
        const qtyHidden = document.getElementById('addToCartQty');
        const form = document.getElementById('addToCartForm');

        if (!qtyInput || !qtyHidden || !form) return;

        const clamp = (value) => {
            const n = parseInt(value, 10);
            if (Number.isNaN(n) || n < 1) return 1;
            return n;
        };

        const sync = () => {
            const v = clamp(qtyInput.value);
            qtyInput.value = String(v);
            qtyHidden.value = String(v);
        };

        sync();

        document.querySelectorAll('[data-qty-action]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const action = btn.getAttribute('data-qty-action');
                const current = clamp(qtyInput.value);
                qtyInput.value = String(action === 'increase' ? current + 1 : Math.max(1, current - 1));
                sync();
            });
        });

        qtyInput.addEventListener('change', sync);
        qtyInput.addEventListener('input', sync);
        form.addEventListener('submit', sync);
    }
    
    // Chạy ngay khi load
    adjustContactTextSize();
    initQtyStepper();
    
    // Chạy lại khi resize
    window.addEventListener('resize', adjustContactTextSize);
    
    // Chạy lại sau khi DOM load xong (để bắt phần JavaScript động tạo "Liên hệ")
    setTimeout(adjustContactTextSize, 500);
    setTimeout(adjustContactTextSize, 1000);
});
</script>
@endpush 