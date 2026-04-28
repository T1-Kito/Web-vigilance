@extends('layouts.admin')

@section('title', ($pageTitle ?? ((($pageMode ?? 'quote') === 'quote') ? 'Sửa báo giá' : 'Xử lý đơn từ Web')))

@section('content')
@php
    $pageMode = $pageMode ?? 'quote';
    $isQuoteMode = $pageMode === 'quote';
    $backRoute = $backRoute ?? 'admin.quotes.index';
    $formAction = $formAction ?? ($isQuoteMode ? (isset($isCreate) && $isCreate ? route('admin.quotes.store') : route('admin.quotes.update', $order)) : route('admin.web-orders.update', $order));
    $submitLabel = $submitLabel ?? ((isset($isCreate) && $isCreate) ? 'Tạo báo giá' : ($isQuoteMode ? 'Lưu báo giá' : 'Lưu xử lý đơn web'));
    $pageHeading = $pageHeading ?? ((isset($isCreate) && $isCreate) ? 'Tạo báo giá mới' : ($isQuoteMode ? ('Sửa báo giá: ' . ($order->quote_code ?? ('VK' . str_pad($order->id, 6, '0', STR_PAD_LEFT)))) : ('Sửa đơn web: ' . ($order->order_code ?? ('OD' . str_pad($order->id, 6, '0', STR_PAD_LEFT))))));
    $orderCode = $order->quote_code ?? $order->order_code ?? ('VK' . str_pad($order->id, 6, '0', STR_PAD_LEFT));

    $oldItems = old('items');
    if (is_array($oldItems) && count($oldItems) > 0) {
        $formItems = collect($oldItems)->values()->map(function ($row) {
            $productId = (int) ($row['product_id'] ?? 0);
            return [
                'id' => isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null,
                'product_id' => $productId,
                'product_name' => $row['product_name'] ?? ('Sản phẩm #' . $productId),
                'serial_number' => $row['serial_number'] ?? null,
                'unit' => $row['unit'] ?? '',
                'quantity' => (int) ($row['quantity'] ?? 1),
                'unit_price' => (float) ($row['unit_price'] ?? 0),
            ];
        })->all();
    } else {
        $formItems = ($order->items ?? collect())->map(function ($item) {
            return [
                'id' => (int) $item->id,
                'product_id' => (int) $item->product_id,
                'product_name' => (string) ($item->product->name ?? ('Sản phẩm #' . $item->product_id)),
                'serial_number' => (string) ($item->product->serial_number ?? ''),
                'unit' => (string) ($item->unit ?? ''),
                'quantity' => (int) ($item->quantity ?? 1),
                'unit_price' => (float) ($item->price ?? 0),
            ];
        })->values()->all();
    }

    if (count($formItems) === 0) {
        $formItems[] = [
            'id' => null,
            'product_id' => 0,
            'product_name' => '',
            'serial_number' => '',
            'unit' => '',
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }

    $subTotal = collect($formItems)->sum(function ($item) {
        return (float) ($item['unit_price'] ?? 0) * (int) ($item['quantity'] ?? 0);
    });

    $discountPreview = (float) old('discount_percent', $order->discount_percent ?? 0);
    $vatPreview = (float) old('vat_percent', $order->vat_percent ?? 8);
    $afterDiscount = max(0, $subTotal * (1 - ($discountPreview / 100)));
    $vatAmount = $afterDiscount * ($vatPreview / 100);
    $grandTotal = $afterDiscount + $vatAmount;
@endphp

<div class="container-fluid py-4 quote-edit-page">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $pageHeading }}</h1>
            <div class="text-muted">Form chuẩn: thêm, xóa, đổi sản phẩm và cập nhật giá/SL trực tiếp.</div>
        </div>
        <div class="d-flex gap-2">
            @if($isQuoteMode && !(isset($isCreate) && $isCreate))
                <a href="{{ route('orders.quote', ['orderCode' => $orderCode]) }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>Xem báo giá
                </a>
                @if(($order->status ?? '') === 'approved' && !optional($order->convertedSalesOrder)->id)
                    <form method="POST" action="{{ route('admin.quotes.convert-to-order', $order) }}" class="d-inline" onsubmit="return confirm('Tạo đơn bán từ báo giá đã duyệt?');">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check2-circle me-1"></i>Tạo đơn bán
                        </button>
                    </form>
                @endif
            @endif
            <a href="{{ route($backRoute) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Quay lại
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" id="quote-edit-form">
        @csrf
        @if(!(isset($isCreate) && $isCreate))
            @method('PATCH')
        @endif

        <div id="workflow-warning-panel" class="alert alert-warning border-0 shadow-sm d-none mb-4">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle me-1"></i>Cảnh báo đối chiếu dữ liệu</div>
            <div class="small mb-0" id="workflow-warning-text">Hệ thống sẽ cảnh báo ngay khi tên hàng trên chứng từ không khớp.</div>
        </div>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">1) Thông tin hóa đơn & liên hệ</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Mã số thuế</label>
                                <input type="text" id="qe-tax-code" name="customer_tax_code" class="form-control" value="{{ old('customer_tax_code', $order->customer_tax_code) }}">
                                <div id="qe-tax-hint" class="form-text small mt-1" style="min-height:1.25rem;"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">SĐT liên hệ</label>
                                <input type="text" id="qe-customer-phone" name="customer_phone" class="form-control" value="{{ old('customer_phone', $order->customer_phone) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" id="qe-customer-email" name="customer_email" class="form-control" value="{{ old('customer_email', $order->customer_email) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tên công ty (HĐ)</label>
                                <input type="text" id="qe-invoice-company" name="invoice_company_name" class="form-control" value="{{ old('invoice_company_name', $order->invoice_company_name) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Người liên hệ (Att)</label>
                                <input type="text" id="qe-contact-person" name="customer_contact_person" class="form-control" value="{{ old('customer_contact_person', $order->customer_contact_person) }}">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Địa chỉ hóa đơn</label>
                                <textarea id="qe-invoice-address" name="invoice_address" class="form-control" rows="2">{{ old('invoice_address', $order->invoice_address) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">2) Thông tin nhận hàng</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Người nhận</label>
                                <input type="text" id="qe-receiver-name" name="receiver_name" class="form-control" value="{{ old('receiver_name', $order->receiver_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SĐT người nhận</label>
                                <input type="text" id="qe-receiver-phone" name="receiver_phone" class="form-control" value="{{ old('receiver_phone', $order->receiver_phone) }}" placeholder="Không bắt buộc">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Địa chỉ giao hàng</label>
                                <textarea id="qe-receiver-address" name="receiver_address" class="form-control" rows="2" required>{{ old('receiver_address', $order->receiver_address) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span>3) Dòng sản phẩm báo giá</span>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-item">
                            <i class="bi bi-plus-lg me-1"></i>Thêm sản phẩm
                        </button>
                    </div>
                <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle quote-items-table">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Sản phẩm</th>
                                        <th style="width:130px;">Đơn vị</th>
                                        <th style="width:110px;">SL</th>
                                        <th style="width:170px;">Đơn giá</th>
                                        <th style="width:70px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="quote-items-body">
                                    @foreach($formItems as $idx => $item)
                                        <tr data-row>
                                            <td class="ps-3">
                                                <input type="hidden" name="items[{{ $idx }}][id]" class="item-id" value="{{ $item['id'] ?? '' }}">
                                                <input type="hidden" name="items[{{ $idx }}][product_id]" class="item-product-id" value="{{ $item['product_id'] ?? '' }}" required>
                                                <input type="hidden" name="items[{{ $idx }}][product_name]" class="item-product-name-input" value="{{ $item['product_name'] ?? '' }}">
                                                <input type="hidden" name="items[{{ $idx }}][serial_number]" class="item-serial-number-input" value="{{ $item['serial_number'] ?? '' }}">
                                                <input type="text" class="form-control item-product-search" placeholder="Tìm theo tên/mã sản phẩm..." value="{{ $item['product_name'] ?? '' }}" autocomplete="off" required>
                                            </td>
                                            <td>
                                                <input type="text" name="items[{{ $idx }}][unit]" class="form-control" value="{{ $item['unit'] ?? '' }}" placeholder="Cái, bộ...">
                                            </td>
                                            <td>
                                                <input type="number" min="1" max="99999" name="items[{{ $idx }}][quantity]" class="form-control item-qty" value="{{ (int) ($item['quantity'] ?? 1) }}" required>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <small class="text-muted item-price-hint mb-0"></small>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary item-price-lock" data-locked="1" title="Mở khóa để sửa tay đơn giá">
                                                        <i class="bi bi-lock"></i>
                                                    </button>
                                                </div>
                                                <input type="number" min="0" step="1" name="items[{{ $idx }}][unit_price]" class="form-control item-unit-price" value="{{ (float) ($item['unit_price'] ?? 0) }}" required readonly>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="Xóa dòng">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card border-0 shadow-sm sticky-xl-top quote-edit-side" style="top: 16px;">
                    <div class="card-header bg-white fw-bold">4) Điều khoản thanh toán & thông số</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select" required>
                                    @foreach($statusOptions as $val => $label)
                                        <option value="{{ $val }}" @selected(old('status', $order->status) === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-xl-12">
                                <label class="form-label">Staff code</label>
                                <input type="text" id="staff-code" name="staff_code" class="form-control" value="{{ old('staff_code', $order->staff_code) }}" placeholder="Tự tạo theo tên sales, có thể sửa tay">
                            </div>
                            <div class="col-md-6 col-xl-12">
                                <label class="form-label">Sales</label>
                                <input type="text" id="sales-name" name="sales_name" class="form-control" list="sales-name-list" value="{{ old('sales_name', $order->sales_name) }}" placeholder="Chọn hoặc nhập tên sales...">
                                <datalist id="sales-name-list">
                                    <option value="Bùi Nguyễn Tường Vy"></option>
                                    <option value="Nguyễn Thị Hồng Vi"></option>
                                </datalist>
                                <div class="form-text">Có thể chọn nhanh từ danh sách hoặc nhập tên khác.</div>
                            </div>

                            <div class="col-md-6 col-xl-12">
                                <label for="qe-customer-type" class="form-label">Áp dụng CK cho khách hàng</label>
                                <select id="qe-customer-type" name="customer_type" class="form-select">
                                    <option value="">-- Chọn loại --</option>
                                    <option value="retail" @selected(old('customer_type', $order->customer_type) === 'retail')>Khách lẻ</option>
                                    <option value="agent" @selected(old('customer_type', $order->customer_type) === 'agent')>Đại lý</option>
                                    <option value="factory" @selected(old('customer_type', $order->customer_type) === 'factory')>Nhà máy</option>
                                    <option value="enterprise" @selected(old('customer_type', $order->customer_type) === 'enterprise')>Doanh nghiệp</option>
                                </select>
                                <div class="form-text">Đơn giá sẽ tự áp theo bảng giá số lượng + loại khách hàng.</div>
                            </div>

                            <div class="col-md-6 col-xl-12 d-none">
                                <label class="form-label">Chiết khấu (%)</label>
                                <input type="number" min="0" max="100" step="0.01" name="discount_percent" class="form-control" id="discount-percent" value="{{ old('discount_percent', $order->discount_percent ?? 0) }}">
                            </div>
                            <div class="col-md-6 col-xl-12 d-none">
                                <label class="form-label">VAT (%)</label>
                                <input type="number" min="0" max="100" step="0.01" name="vat_percent" class="form-control" id="vat-percent" value="{{ old('vat_percent', $order->vat_percent ?? 8) }}">
                            </div>

                            <div class="col-12">
                                <div class="border rounded-3 p-3 bg-light-subtle">
                                    <div class="fw-semibold mb-2">Điều khoản thanh toán</div>
                                    <div class="d-grid gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input js-payment-term" type="radio" name="payment_term" id="pt-full" value="full_advance" @checked(old('payment_term', $order->payment_term ?? 'full_advance') === 'full_advance')>
                                            <label class="form-check-label" for="pt-full">Thanh toán 100% trước giao hàng</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input js-payment-term" type="radio" name="payment_term" id="pt-debt" value="debt" @checked(old('payment_term', $order->payment_term ?? '') === 'debt')>
                                            <label class="form-check-label" for="pt-debt">Công nợ theo hạn thanh toán</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input js-payment-term" type="radio" name="payment_term" id="pt-deposit" value="deposit" @checked(old('payment_term', $order->payment_term ?? '') === 'deposit')>
                                            <label class="form-check-label" for="pt-deposit">Đặt cọc + thanh toán phần còn lại</label>
                                        </div>
                                    </div>

                                    <div class="mt-3" id="wrap-payment-due-days">
                                        <label class="form-label mb-1">Số ngày công nợ</label>
                                        <input type="number" min="0" max="365" step="1" name="payment_due_days" id="payment-due-days" class="form-control" value="{{ old('payment_due_days', $order->payment_due_days) }}" placeholder="VD: 15">
                                    </div>

                                    <div class="mt-3" id="wrap-deposit-percent">
                                        <label class="form-label mb-1">Tỷ lệ đặt cọc (%)</label>
                                        <input type="number" min="0" max="100" step="0.01" name="deposit_percent" id="deposit-percent" class="form-control" value="{{ old('deposit_percent', $order->deposit_percent) }}" placeholder="VD: 30">
                                    </div>

                                    <div class="mt-3">
                                        <label class="form-label mb-1">Ghi chú thanh toán</label>
                                        <input type="text" name="payment_note" class="form-control" value="{{ old('payment_note', $order->payment_note) }}" placeholder="VD: Thanh toán phần còn lại khi nhận đủ hàng">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 col-xl-12">
                                <label class="form-label">Hiệu lực báo giá (đến ngày)</label>
                                <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', optional($order->valid_until)->format('Y-m-d') ?? now()->addDays(15)->format('Y-m-d')) }}">
                                <div class="form-text">Mặc định 15 ngày kể từ hôm nay.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Ghi chú</label>
                                <textarea name="note" class="form-control" rows="3">{{ old('note', $order->note) }}</textarea>
                            </div>
                        </div>

                        <hr>

                        <div class="small text-muted mb-1">Tạm tính theo dữ liệu hiện tại</div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Tạm tính:</span>
                            <strong id="sum-subtotal">{{ number_format($subTotal, 0, ',', '.') }}đ</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Sau chiết khấu:</span>
                            <strong id="sum-after-discount">{{ number_format($afterDiscount, 0, ',', '.') }}đ</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>VAT:</span>
                            <strong id="sum-vat">{{ number_format($vatAmount, 0, ',', '.') }}đ</strong>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <span class="fw-semibold">Tổng cộng:</span>
                            <strong class="text-danger" id="sum-total">{{ number_format($grandTotal, 0, ',', '.') }}đ</strong>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary" @if($isQuoteMode && !(isset($isCreate) && $isCreate) && (((($order->status ?? '') === 'won') || optional($order->convertedSalesOrder)->id))) disabled @endif>
                                <i class="bi bi-check2-circle me-1"></i>{{ $submitLabel }}
                            </button>
                            <a href="{{ route($backRoute) }}" class="btn btn-light border">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="quote-item-row-template">
    <tr data-row>
        <td class="ps-3">
            <input type="hidden" name="items[__I__][id]" class="item-id" value="">
            <input type="hidden" name="items[__I__][product_id]" class="item-product-id" value="" required>
            <input type="hidden" name="items[__I__][product_name]" class="item-product-name-input" value="">
            <input type="hidden" name="items[__I__][serial_number]" class="item-serial-number-input" value="">
            <input type="text" class="form-control item-product-search" placeholder="Tìm theo tên/mã sản phẩm..." value="" autocomplete="off" required>
        </td>
        <td>
            <input type="text" name="items[__I__][unit]" class="form-control" value="" placeholder="Cái, bộ...">
        </td>
        <td>
            <input type="number" min="1" max="99999" name="items[__I__][quantity]" class="form-control item-qty" value="1" required>
        </td>
        <td>
            <div class="d-flex align-items-center justify-content-between mb-1">
                <small class="text-muted item-price-hint mb-0">Giá tự động theo loại khách hàng + số lượng</small>
                <button type="button" class="btn btn-xs btn-outline-secondary item-price-lock" data-locked="1" title="Mở khóa để sửa tay đơn giá">
                    <i class="bi bi-lock"></i>
                </button>
            </div>
            <input type="number" min="0" step="1" name="items[__I__][unit_price]" class="form-control item-unit-price" value="0" required readonly>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" title="Xóa dòng">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

<style>
    .quote-items-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-size: .75rem;
        color: #64748b;
        font-weight: 700;
    }
    .quote-items-table tbody td {
        border-color: #edf2f7;
        vertical-align: bottom;
    }
    .quote-product-suggest {
        display: none;
        position: fixed;
        z-index: 2100;
        background: #fff;
        border: 1px solid #dbe2ea;
        border-radius: 10px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.14);
        max-height: 280px;
        overflow-y: auto;
        padding: 6px;
    }
    .quote-product-suggest button {
        width: 100%;
        border: 0;
        background: transparent;
        text-align: left;
        padding: 9px 10px;
        border-radius: 8px;
        font-size: .88rem;
        color: #334155;
    }
    .quote-product-suggest button:hover {
        background: #f1f5f9;
    }
    .btn.btn-xs {
        --bs-btn-padding-y: .1rem;
        --bs-btn-padding-x: .35rem;
        --bs-btn-font-size: .72rem;
        line-height: 1.2;
    }
</style>

<script>
(function () {
    const form = document.getElementById('quote-edit-form');
    const tbody = document.getElementById('quote-items-body');
    const addBtn = document.getElementById('btn-add-item');
    const tpl = document.getElementById('quote-item-row-template');

    const discountInput = document.getElementById('discount-percent');
    const vatInput = document.getElementById('vat-percent');
    const salesNameInput = document.getElementById('sales-name');
    const staffCodeInput = document.getElementById('staff-code');

    const subTotalEl = document.getElementById('sum-subtotal');
    const afterDiscountEl = document.getElementById('sum-after-discount');
    const vatEl = document.getElementById('sum-vat');
    const totalEl = document.getElementById('sum-total');

    const LOOKUP_URL = @json(route('admin.products.lookup'));
    const LINE_OPT_BASE = @json(url('/cp-admin/orders/line-options'));
    const CUSTOMERS_LOOKUP = @json(route('admin.customers.lookup'));
    const TAX_URL_TPL = @json(route('admin.customers.taxLookup', ['taxCode' => '__TAX__']));

    const taxInput = document.getElementById('qe-tax-code');
    const taxHint = document.getElementById('qe-tax-hint');
    const invoiceCompanyInput = document.getElementById('qe-invoice-company');
    const invoiceAddressInput = document.getElementById('qe-invoice-address');
    const contactPersonInput = document.getElementById('qe-contact-person');
    const customerPhoneInput = document.getElementById('qe-customer-phone');
    const customerEmailInput = document.getElementById('qe-customer-email');
    const receiverNameInput = document.getElementById('qe-receiver-name');
    const receiverPhoneInput = document.getElementById('qe-receiver-phone');
    const receiverAddressInput = document.getElementById('qe-receiver-address');
    const workflowWarningPanel = document.getElementById('workflow-warning-panel');
    const workflowWarningText = document.getElementById('workflow-warning-text');

    let suggestEl = null;
    let activeInput = null;
    let debounceTimer = null;
    let taxTimer = null;
    let lineCounter = tbody.querySelectorAll('tr[data-row]').length;

    function money(v) {
        const n = Number(v || 0);
        return (isFinite(n) ? n : 0).toLocaleString('vi-VN') + 'đ';
    }

    function generateStaffCodeFromName(fullName) {
        const source = String(fullName || '').trim();
        if (!source) return '';

        const normalized = source
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/Đ/g, 'D')
            .toLowerCase();

        const parts = normalized.split(/\s+/).filter(Boolean);
        if (!parts.length) return '';

        if (parts.length === 1) {
            return parts[0].slice(0, 4);
        }

        const last = parts[parts.length - 1];
        const initials = parts.slice(0, -1).map(function (p) { return p.charAt(0); }).join('');
        return (initials + last).replace(/[^a-z0-9]/g, '').slice(0, 12);
    }

    function safeNumber(v, fallback = 0) {
        const n = Number(v);
        return isFinite(n) ? n : fallback;
    }

    function normalizeTaxCode(s) {
        return String(s || '').replace(/\s+/g, '').trim();
    }

    function normalizeTaxDigits(s) {
        return String(s || '').replace(/\D/g, '');
    }

    function isTaxLike(q) {
        const c = normalizeTaxCode(q);
        return /^[\d\-]{8,}$/.test(c);
    }

    function setTaxHint(msg, cls) {
        if (!taxHint) return;
        taxHint.textContent = msg || '';
        taxHint.className = 'form-text small mt-1 ' + (cls || 'text-muted');
    }

    async function lookupCustomersByTax(q) {
        const url = CUSTOMERS_LOOKUP + '?q=' + encodeURIComponent(q);
        const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        if (!res.ok) return [];
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    function pickCustomerExactTax(rows, typedDigits) {
        if (!rows || !rows.length || !typedDigits) return null;
        return rows.find(function (r) {
            return normalizeTaxDigits(r.tax_id || '') === typedDigits;
        }) || null;
    }

    async function fetchTaxLookup(code) {
        const url = TAX_URL_TPL.replace('__TAX__', encodeURIComponent(code));
        const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        let json = null;
        try {
            json = await res.json();
        } catch (e) { json = null; }
        return { ok: res.ok, json };
    }

    function setWorkflowWarning(message, show = true) {
        if (!workflowWarningPanel || !workflowWarningText) return;
        workflowWarningText.textContent = message || '';
        workflowWarningPanel.classList.toggle('d-none', !show);
    }

    function normalizeName(s) {
        return String(s || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/[^a-z0-9\s]/g, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function similarText(a, b) {
        const aa = normalizeName(a);
        const bb = normalizeName(b);
        if (!aa || !bb) return 1;
        if (aa === bb) return 0;
        const aParts = aa.split(' ');
        const bParts = bb.split(' ');
        const overlap = aParts.filter(function (x) { return bParts.includes(x); }).length;
        const maxLen = Math.max(aParts.length, bParts.length, 1);
        return 1 - (overlap / maxLen);
    }

    function applyTaxPayload(d) {
        if (!d) return;
        const v = function (x) { return (x != null && String(x).trim() !== '') ? String(x).trim() : ''; };
        const setIf = function (node, val) {
            if (!node || val === '') return;
            node.value = val;
        };

        setIf(invoiceCompanyInput, v(d.name));
        setIf(invoiceAddressInput, v(d.tax_address) || v(d.address));
        setIf(customerPhoneInput, v(d.phone));
        setIf(customerEmailInput, v(d.email));
        setIf(contactPersonInput, v(d.representative) || v(d.invoice_recipient));

        const shipName = v(d.representative) || v(d.name);
        if (receiverNameInput && (!receiverNameInput.value.trim() || receiverNameInput.dataset.autofill === '1')) {
            if (shipName) {
                receiverNameInput.value = shipName;
                receiverNameInput.dataset.autofill = '1';
            }
        }

        if (receiverPhoneInput && (!receiverPhoneInput.value.trim() || receiverPhoneInput.dataset.autofill === '1')) {
            const ph = v(d.phone);
            if (ph) {
                receiverPhoneInput.value = ph;
                receiverPhoneInput.dataset.autofill = '1';
            }
        }

        const shipAddr = v(d.address) || v(d.tax_address);
        if (receiverAddressInput && (!receiverAddressInput.value.trim() || receiverAddressInput.dataset.autofill === '1')) {
            if (shipAddr) {
                receiverAddressInput.value = shipAddr;
                receiverAddressInput.dataset.autofill = '1';
            }
        }
    }

    ['input', 'change'].forEach(function (evt) {
        [receiverNameInput, receiverPhoneInput, receiverAddressInput].forEach(function (node) {
            if (!node) return;
            node.addEventListener(evt, function () { node.dataset.autofill = '0'; });
        });
    });

    if (taxInput) {
        taxInput.addEventListener('input', function () {
            const code = normalizeTaxCode(taxInput.value);
            if (taxTimer) clearTimeout(taxTimer);

            validateWorkflowWarnings();

            if (!isTaxLike(code)) {
                setTaxHint('Nhập MST (ít nhất 8 ký tự)', 'text-muted');
                return;
            }

            setTaxHint('Đang kiểm tra danh sách khách hàng…', 'text-muted');
            taxTimer = setTimeout(async function () {
                const typedDigits = normalizeTaxDigits(code);
                const rows = await lookupCustomersByTax(code);
                const local = pickCustomerExactTax(rows, typedDigits);

                if (local) {
                    setTaxHint('Đã điền từ khách hàng nội bộ (CRM).', 'text-success');
                    applyTaxPayload({
                        name: local.name || '',
                        tax_address: local.tax_address || '',
                        address: local.address || '',
                        phone: local.phone || '',
                        email: local.email || '',
                        representative: local.representative || '',
                        invoice_recipient: local.invoice_recipient || '',
                    });
                    return;
                }

                setTaxHint('Không có trong CRM — đang tra cứu nguồn thuế công khai…', 'text-muted');
                const { ok, json } = await fetchTaxLookup(code);
                if (!ok || !json || json.ok !== true || !json.data) {
                    const msg = (json && json.message) ? String(json.message) : 'Không tra cứu được MST ngoài.';
                    setTaxHint(msg, 'text-danger');
                    return;
                }

                setTaxHint('Đã điền từ tra cứu ngoài (không có trong danh sách khách hàng).', 'text-success');
                applyTaxPayload(json.data);
            }, 450);
        });
    }

    const paymentTermInputs = Array.from(document.querySelectorAll('.js-payment-term'));
    const wrapDueDays = document.getElementById('wrap-payment-due-days');
    const wrapDeposit = document.getElementById('wrap-deposit-percent');
    const dueDaysInput = document.getElementById('payment-due-days');
    const depositInput = document.getElementById('deposit-percent');

    function selectedPaymentTerm() {
        const checked = paymentTermInputs.find(function (x) { return x.checked; });
        return checked ? checked.value : 'full_advance';
    }

    function togglePaymentBlocks() {
        const term = selectedPaymentTerm();
        const showDueDays = term === 'debt';
        const showDeposit = term === 'deposit';

        if (wrapDueDays) wrapDueDays.style.display = showDueDays ? '' : 'none';
        if (wrapDeposit) wrapDeposit.style.display = showDeposit ? '' : 'none';

        if (dueDaysInput) dueDaysInput.required = showDueDays;
        if (depositInput) depositInput.required = showDeposit;
    }

    paymentTermInputs.forEach(function (input) {
        input.addEventListener('change', togglePaymentBlocks);
    });
    togglePaymentBlocks();

    function recalcSummary() {
        let subtotal = 0;
        tbody.querySelectorAll('tr[data-row]').forEach(function (row) {
            const qty = safeNumber(row.querySelector('.item-qty')?.value, 0);
            const price = safeNumber(row.querySelector('.item-unit-price')?.value, 0);
            subtotal += Math.max(0, qty) * Math.max(0, price);
        });

        const discount = Math.max(0, Math.min(100, safeNumber(discountInput?.value, 0)));
        const vat = Math.max(0, Math.min(100, safeNumber(vatInput?.value, 0)));

        const afterDiscount = Math.max(0, subtotal * (1 - discount / 100));
        const vatAmount = afterDiscount * (vat / 100);
        const total = afterDiscount + vatAmount;

        subTotalEl.textContent = money(subtotal);
        afterDiscountEl.textContent = money(afterDiscount);
        vatEl.textContent = money(vatAmount);
        totalEl.textContent = money(total);
        validateWorkflowWarnings();
    }

    function getRowsItemNames() {
        return Array.from(tbody.querySelectorAll('tr[data-row]')).map(function (row) {
            return String(row.querySelector('.item-product-search')?.value || '').trim();
        }).filter(Boolean);
    }

    function validateWorkflowWarnings() {
        const quoteCompany = String(invoiceCompanyInput?.value || receiverNameInput?.value || '').trim();
        const rowNames = getRowsItemNames();
        const warningParts = [];
        const orderName = String(receiverNameInput?.value || '').trim();

        if (quoteCompany && rowNames.length) {
            const mismatched = rowNames.filter(function (name) {
                return similarText(name, quoteCompany) > 0.5;
            });
            if (mismatched.length) {
                warningParts.push('Tên hàng trên báo giá có thể không khớp với tên công ty/khách hàng đã nhập.');
            }
        }

        if (orderName && quoteCompany && normalizeName(orderName) !== normalizeName(quoteCompany)) {
            warningParts.push('Thông tin người nhận và tên công ty đang khác nhau, vui lòng kiểm tra lại trước khi phát hành.');
        }

        if (warningParts.length > 0) {
            setWorkflowWarning(warningParts.join(' '), true);
        } else {
            setWorkflowWarning('', false);
        }
    }

    function ensureSuggest() {
        if (suggestEl) return suggestEl;
        suggestEl = document.createElement('div');
        suggestEl.className = 'quote-product-suggest';
        document.body.appendChild(suggestEl);
        return suggestEl;
    }

    function hideSuggest() {
        if (suggestEl) suggestEl.style.display = 'none';
        activeInput = null;
    }

    function positionSuggest(input) {
        const box = ensureSuggest();
        const rect = input.getBoundingClientRect();
        const width = Math.max(rect.width, 340);
        box.style.left = Math.max(8, Math.min(rect.left, window.innerWidth - width - 8)) + 'px';
        box.style.top = (rect.bottom + 6) + 'px';
        box.style.width = width + 'px';
    }

    function getQuoteCustomerType() {
        const el = document.getElementById('qe-customer-type');
        return el ? String(el.value || '').trim() : '';
    }

    function isRowPriceLocked(row) {
        return row?.querySelector('.item-price-lock')?.dataset.locked !== '0';
    }

    function syncLockButtonUI(btn, locked) {
        if (!btn) return;
        btn.dataset.locked = locked ? '1' : '0';
        btn.classList.toggle('btn-outline-secondary', locked);
        btn.classList.toggle('btn-outline-warning', !locked);
        btn.title = locked ? 'Mở khóa để sửa tay đơn giá' : 'Khóa lại để hệ thống tự áp đơn giá';
        const icon = btn.querySelector('i');
        if (icon) icon.className = locked ? 'bi bi-lock' : 'bi bi-unlock';
    }

    function loadAutoUnitPriceForRow(row) {
        const productId = row.querySelector('.item-product-id')?.value;
        const qty = Math.max(1, Number(row.querySelector('.item-qty')?.value || 1));
        const customerType = getQuoteCustomerType();
        const priceInput = row.querySelector('.item-unit-price');
        const hintEl = row.querySelector('.item-price-hint');
        if (!productId || !priceInput) return;

        if (!isRowPriceLocked(row)) {
            if (hintEl) {
                hintEl.textContent = 'Đang sửa tay đơn giá (đã mở khóa).';
                hintEl.className = 'text-warning item-price-hint mb-0';
            }
            recalcSummary();
            return;
        }

        const url = `${LINE_OPT_BASE}/${productId}?quantity=${encodeURIComponent(qty)}${customerType ? `&customer_type=${encodeURIComponent(customerType)}` : ''}`;
        fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        })
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(function (json) {
                if (!json) return;
                priceInput.value = Math.round(Number(json.final_price || json.base_price || 0));

                if (hintEl) {
                    const customerMap = {
                        retail: 'Khách lẻ',
                        agent: 'Đại lý',
                        factory: 'Nhà máy',
                        enterprise: 'Doanh nghiệp',
                        all: 'Tất cả',
                    };

                    if (!customerType) {
                        hintEl.textContent = 'giá mặc định.';
                        hintEl.className = 'text-muted item-price-hint mb-0';
                    } else if (!json.tier) {
                        const source = String(json.price_source || 'base_price');
                        if (source === 'factory_price') {
                            hintEl.textContent = 'Đang áp đơn giá Nhà máy từ sản phẩm.';
                            hintEl.className = 'text-success item-price-hint mb-0';
                        } else if (source === 'agency_price') {
                            hintEl.textContent = 'Đang áp đơn giá Đại lý từ sản phẩm.';
                            hintEl.className = 'text-success item-price-hint mb-0';
                        } else if (source === 'retail_price') {
                            hintEl.textContent = 'Đang áp đơn giá Khách lẻ từ sản phẩm.';
                            hintEl.className = 'd-block mt-1 text-success item-price-hint';
                        } else {
                            hintEl.textContent = `Chưa có bảng giá ${customerMap[customerType] || customerType} cho sản phẩm này.`;
                            hintEl.className = 'text-warning item-price-hint mb-0';
                        }
                    } else {
                        const tierType = String(json.tier.customer_type || 'all');
                        if (tierType === customerType) {
                            hintEl.textContent = `Đang áp giá ${customerMap[tierType] || tierType} theo số lượng.`;
                            hintEl.className = 'd-block mt-1 text-success item-price-hint';
                        } else if (tierType === 'all') {
                            hintEl.textContent = `Không có giá ${customerMap[customerType] || customerType}, đang dùng mức Tất cả.`;
                            hintEl.className = 'text-warning item-price-hint mb-0';
                        } else {
                            hintEl.textContent = `Đang áp mức ${customerMap[tierType] || tierType}.`;
                            hintEl.className = 'text-muted item-price-hint mb-0';
                        }
                    }
                }

                recalcSummary();
            })
            .catch(function () {});
    }

    function applyAutoPriceForAllRows() {
        tbody.querySelectorAll('tr[data-row]').forEach(function (row) {
            loadAutoUnitPriceForRow(row);
        });
    }


    function setSelectedProduct(row, product) {
        const idInput = row.querySelector('.item-product-id');
        const nameHidden = row.querySelector('.item-product-name-input');
        const serialHidden = row.querySelector('.item-serial-number-input');
        const searchInput = row.querySelector('.item-product-search');

        idInput.value = product.id;
        nameHidden.value = product.name || '';
        serialHidden.value = product.serial_number || '';
        searchInput.value = product.name || '';

        loadAutoUnitPriceForRow(row);
    }

    function renderSuggest(input, rows) {
        const box = ensureSuggest();
        if (!rows || !rows.length) {
            hideSuggest();
            return;
        }

        positionSuggest(input);
        box.innerHTML = '';

        rows.forEach(function (p) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = p.name + (p.serial_number ? (' · ' + p.serial_number) : '');
            btn.addEventListener('mousedown', function (e) { e.preventDefault(); });
            btn.addEventListener('click', function () {
                const row = input.closest('tr[data-row]');
                if (row) setSelectedProduct(row, p);
                hideSuggest();
            });
            box.appendChild(btn);
        });

        box.style.display = 'block';
    }

    async function searchProducts(q) {
        const url = LOOKUP_URL + '?q=' + encodeURIComponent(q);
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        });
        if (!res.ok) return [];
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    function bindRow(row) {
        const lockBtn = row.querySelector('.item-price-lock');
        const priceInput = row.querySelector('.item-unit-price');
        if (lockBtn && priceInput) {
            syncLockButtonUI(lockBtn, lockBtn.dataset.locked !== '0');
            lockBtn.addEventListener('click', function () {
                const willLock = lockBtn.dataset.locked === '0';
                syncLockButtonUI(lockBtn, willLock);
                priceInput.readOnly = willLock;

                if (willLock) {
                    loadAutoUnitPriceForRow(row);
                } else {
                    const hintEl = row.querySelector('.item-price-hint');
                    if (hintEl) {
                        hintEl.textContent = 'Đang sửa tay đơn giá (đã mở khóa).';
                        hintEl.className = 'text-warning item-price-hint mb-0';
                    }
                    priceInput.focus();
                    priceInput.select();
                }
            });
        }

        row.querySelector('.btn-remove-item')?.addEventListener('click', function () {
            const rows = tbody.querySelectorAll('tr[data-row]');
            if (rows.length <= 1) {
                alert('Báo giá phải có ít nhất 1 sản phẩm.');
                return;
            }
            row.remove();
            recalcSummary();
        });

        row.querySelector('.item-qty')?.addEventListener('input', function () {
            if (isRowPriceLocked(row)) {
                loadAutoUnitPriceForRow(row);
            } else {
                recalcSummary();
            }
        });

        row.querySelector('.item-unit-price')?.addEventListener('input', recalcSummary);

        const searchInput = row.querySelector('.item-product-search');
        if (searchInput) {
            searchInput.addEventListener('focus', function () {
                activeInput = searchInput;
            });

            searchInput.addEventListener('input', function () {
                const q = String(searchInput.value || '').trim();
                const rowEl = searchInput.closest('tr[data-row]');
                if (!rowEl) return;

                if (q.length < 2) {
                    hideSuggest();
                    const idInput = rowEl.querySelector('.item-product-id');
                    idInput.value = '';
                    return;
                }

                if (debounceTimer) clearTimeout(debounceTimer);
                debounceTimer = setTimeout(async function () {
                    try {
                        const rows = await searchProducts(q);
                        renderSuggest(searchInput, rows);
                    } catch (e) {
                        hideSuggest();
                    }
                }, 260);
            });
        }
    }

    function addRow() {
        const html = tpl.innerHTML.replace(/__I__/g, String(lineCounter++));
        const wrap = document.createElement('tbody');
        wrap.innerHTML = html.trim();
        const row = wrap.firstElementChild;
        tbody.appendChild(row);
        bindRow(row);
        row.querySelector('.item-product-search')?.focus();
        recalcSummary();
    }

    addBtn?.addEventListener('click', addRow);
    discountInput?.addEventListener('input', recalcSummary);
    vatInput?.addEventListener('input', recalcSummary);

    const quoteCustomerTypeEl = document.getElementById('qe-customer-type');
    quoteCustomerTypeEl?.addEventListener('change', function () {
        applyAutoPriceForAllRows();
    });

    if (salesNameInput && staffCodeInput) {
        salesNameInput.addEventListener('input', function () {
            const code = generateStaffCodeFromName(salesNameInput.value);
            if (code) {
                staffCodeInput.value = code;
            }
        });

        if (!String(staffCodeInput.value || '').trim() && String(salesNameInput.value || '').trim()) {
            const code = generateStaffCodeFromName(salesNameInput.value);
            if (code) {
                staffCodeInput.value = code;
            }
        }
    }

    tbody.querySelectorAll('tr[data-row]').forEach(bindRow);

    [invoiceCompanyInput, receiverNameInput, receiverPhoneInput, receiverAddressInput, taxInput, salesNameInput].forEach(function (node) {
        node?.addEventListener('input', validateWorkflowWarnings);
        node?.addEventListener('change', validateWorkflowWarnings);
    });

    document.addEventListener('click', function (e) {
        if (!suggestEl) return;
        if (suggestEl.contains(e.target)) return;
        if (e.target.closest('.item-product-search')) return;
        hideSuggest();
    });

    window.addEventListener('scroll', function () {
        if (suggestEl && suggestEl.style.display === 'block' && activeInput) {
            positionSuggest(activeInput);
        }
    }, true);
    window.addEventListener('resize', function () {
        if (suggestEl && suggestEl.style.display === 'block' && activeInput) {
            positionSuggest(activeInput);
        }
    });

    form?.addEventListener('submit', function (e) {
        const rows = tbody.querySelectorAll('tr[data-row]');
        if (rows.length === 0) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất 1 sản phẩm.');
            return;
        }

        let invalid = false;
        rows.forEach(function (row) {
            const productId = row.querySelector('.item-product-id')?.value;
            if (!productId) invalid = true;
        });

        if (invalid) {
            e.preventDefault();
            alert('Có dòng sản phẩm chưa chọn đúng sản phẩm. Vui lòng kiểm tra lại.');
            return;
        }

        validateWorkflowWarnings();
    });

    validateWorkflowWarnings();

    // Giữ nguyên đơn giá đã lưu khi mở lại màn hình sửa báo giá.
    // Chỉ tự áp giá khi user thao tác (đổi loại KH / đổi SL / chọn sản phẩm mới).
    tbody.querySelectorAll('tr[data-row]').forEach(function (row) {
        const hintEl = row.querySelector('.item-price-hint');
        if (!hintEl) return;

        if (isRowPriceLocked(row)) {
            hintEl.textContent = 'Đang khóa đơn giá (giữ giá đã lưu).';
            hintEl.className = 'text-muted item-price-hint mb-0';
        } else {
            hintEl.textContent = 'Đang sửa tay đơn giá (đã mở khóa).';
            hintEl.className = 'text-warning item-price-hint mb-0';
        }
    });
})();
</script>
@endsection
