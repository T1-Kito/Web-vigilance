@extends('layouts.admin')

@section('title', 'Sửa bảo hành')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-pencil"></i> Sửa bảo hành
            </h1>
            <p class="text-muted">Cập nhật thông tin bảo hành</p>
        </div>
        <a href="{{ route('admin.warranties.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin bảo hành</h6>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.warranties.update', $warranty) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="serial_number" class="form-label fw-bold">Số seri (SN) *</label>
                                    <input type="text" 
                                           class="form-control @error('serial_number') is-invalid @enderror" 
                                           id="serial_number" 
                                           name="serial_number" 
                                           value="{{ old('serial_number', $warranty->serial_number) }}" 
                                           required>
                                    @error('serial_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="model_name" class="form-label fw-bold">Tên sản phẩm / Model</label>
                                    <input type="text" 
                                           class="form-control @error('model_name') is-invalid @enderror" 
                                           id="model_name" 
                                           name="model_name" 
                                           value="{{ old('model_name', $warranty->model_name) }}" 
                                           placeholder="Ví dụ: K50PRO, K20PRO...">
                                    <small class="text-muted">Gõ tự do tên model; có thể khác với tên sản phẩm gốc.</small>
                                    @error('model_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="customer_tax_id" class="form-label fw-bold">Mã số thuế</label>
                            <input type="text"
                                   class="form-control @error('customer_tax_id') is-invalid @enderror"
                                   id="customer_tax_id"
                                   name="customer_tax_id"
                                   value="{{ old('customer_tax_id', $warranty->customer_tax_id) }}"
                                   placeholder="Không bắt buộc ">
                            <small class="text-muted">Không bắt buộc. Gõ MST để tự điền thông tin CRM.</small>
                            @error('customer_tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_name" class="form-label fw-bold">Tên khách hàng</label>
                                    <input type="text" 
                                           class="form-control @error('customer_name') is-invalid @enderror" 
                                           id="customer_name" 
                                           name="customer_name" 
                                           value="{{ old('customer_name', $warranty->customer_name) }}"
                                           list="warrantyCustomerDatalist"
                                           autocomplete="off"
                                           placeholder="Tên khách hàng">
                                    <datalist id="warrantyCustomerDatalist"></datalist>
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="customer_phone" class="form-label fw-bold">Số điện thoại</label>
                                    <input type="text" 
                                           class="form-control @error('customer_phone') is-invalid @enderror" 
                                           id="customer_phone" 
                                           name="customer_phone" 
                                           value="{{ old('customer_phone', $warranty->customer_phone) }}">
                                    @error('customer_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="customer_email" class="form-label fw-bold">Email</label>
                            <input type="email" 
                                   class="form-control @error('customer_email') is-invalid @enderror" 
                                   id="customer_email" 
                                   name="customer_email" 
                                   value="{{ old('customer_email', $warranty->customer_email) }}">
                            @error('customer_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="customer_address" class="form-label fw-bold">Địa chỉ</label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" 
                                      id="customer_address" 
                                      name="customer_address" 
                                      rows="2">{{ old('customer_address', $warranty->customer_address) }}</textarea>
                            @error('customer_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-2">
                            <div class="col-6 col-xl-3">
                                <div class="mb-3">
                                    <label for="stock_in_date" class="form-label fw-bold">Ngày nhập hàng</label>
                                    <input type="date" 
                                           class="form-control @error('stock_in_date') is-invalid @enderror"
                                           id="stock_in_date"
                                           name="stock_in_date"
                                           value="{{ old('stock_in_date', optional($warranty->stock_in_date)->format('Y-m-d')) }}"
                                           max="{{ now()->toDateString() }}">
                                    <small class="text-muted">Tuỳ chọn.</small>
                                    @error('stock_in_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6 col-xl-3">
                                <div class="mb-3">
                                    <label for="purchase_date" class="form-label fw-bold">Ngày mua *</label>
                                    <input type="date"
                                           class="form-control @error('purchase_date') is-invalid @enderror" 
                                           id="purchase_date" 
                                           name="purchase_date" 
                                           value="{{ old('purchase_date', $warranty->purchase_date->format('Y-m-d')) }}"
                                           max="{{ now()->toDateString() }}"
                                           required>
                                    @error('purchase_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6 col-xl-3">
                                <div class="mb-3">
                                    <label for="warranty_start_date" class="form-label fw-bold">Ngày bắt đầu bảo hành *</label>
                                    <input type="date" 
                                           class="form-control @error('warranty_start_date') is-invalid @enderror" 
                                           id="warranty_start_date" 
                                           name="warranty_start_date" 
                                           value="{{ old('warranty_start_date', $warranty->warranty_start_date->format('Y-m-d')) }}"
                                           max="{{ now()->toDateString() }}"
                                           required>
                                    @error('warranty_start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6 col-xl-3">
                                <div class="mb-3">
                                    <label for="warranty_period_months" class="form-label fw-bold">Thời hạn bảo hành (tháng) *</label>
                                    <input type="number" 
                                           class="form-control @error('warranty_period_months') is-invalid @enderror" 
                                           id="warranty_period_months" 
                                           name="warranty_period_months" 
                                           value="{{ old('warranty_period_months', $warranty->warranty_period_months) }}" 
                                           min="1" 
                                           max="60" 
                                           required>
                                    @error('warranty_period_months')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="invoice_number" class="form-label fw-bold">Mã hóa đơn</label>
                            <input type="text" 
                                   class="form-control @error('invoice_number') is-invalid @enderror" 
                                   id="invoice_number" 
                                   name="invoice_number" 
                                   value="{{ old('invoice_number', $warranty->invoice_number) }}">
                            @error('invoice_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Lưu ý:</strong> Trạng thái bảo hành sẽ được tự động xác định dựa trên ngày kết thúc bảo hành.
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label fw-bold">Ghi chú</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3">{{ old('notes', $warranty->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Cập nhật
                            </button>
                            <a href="{{ route('admin.warranties.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin hiện tại</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Ngày kết thúc bảo hành:</label>
                        <p class="mb-0">{{ $warranty->warranty_end_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Trạng thái:</label>
                        <p class="mb-0">
                            <span class="badge bg-{{ $warranty->warranty_status_color }}">
                                {{ $warranty->warranty_status_text }}
                            </span>
                        </p>
                    </div>
                    @if($warranty->is_expired)
                        <div class="mb-3">
                            <label class="fw-bold text-muted">Trạng thái:</label>
                            <p class="mb-0 text-danger">{{ $warranty->expired_time_text }}</p>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="fw-bold text-muted">Còn lại:</label>
                            <p class="mb-0 text-success">{{ $warranty->remaining_time_text }}</p>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Số yêu cầu bảo hành:</label>
                        <p class="mb-0">{{ $warranty->claims->count() }} yêu cầu</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const CUSTOMERS_LOOKUP = @json(route('admin.customers.lookup'));
            const purchaseDate = document.getElementById('purchase_date');
            const warrantyStartDate = document.getElementById('warranty_start_date');
            const warrantyPeriod = document.getElementById('warranty_period_months');
            const customerName = document.getElementById('customer_name');
            const customerTaxId = document.getElementById('customer_tax_id');
            const customerPhone = document.getElementById('customer_phone');
            const customerEmail = document.getElementById('customer_email');
            const customerAddress = document.getElementById('customer_address');
            const customerDatalist = document.getElementById('warrantyCustomerDatalist');
            var customerCache = {};
            var nameTimer = null;
            var taxTimer = null;

            function syncWarrantyStartFromPurchase() {
                if (purchaseDate && warrantyStartDate && purchaseDate.value) {
                    warrantyStartDate.value = purchaseDate.value;
                    warrantyStartDate.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
            if (purchaseDate) {
                purchaseDate.addEventListener('change', syncWarrantyStartFromPurchase);
            }

            function calculateEndDate() {
                if (warrantyStartDate && warrantyPeriod && warrantyStartDate.value && warrantyPeriod.value) {
                    const startDate = new Date(warrantyStartDate.value);
                    const endDate = new Date(startDate);
                    endDate.setMonth(endDate.getMonth() + parseInt(warrantyPeriod.value, 10));
                    console.log('Ngày kết thúc bảo hành sẽ là:', endDate.toISOString().split('T')[0]);
                }
            }
            if (warrantyStartDate) warrantyStartDate.addEventListener('change', calculateEndDate);
            if (warrantyPeriod) warrantyPeriod.addEventListener('change', calculateEndDate);

            async function lookupCustomers(q) {
                const res = await fetch(CUSTOMERS_LOOKUP + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (!res.ok) return [];
                const data = await res.json();
                return Array.isArray(data) ? data : [];
            }

            function renderCustomerDatalist(rows) {
                if (!customerDatalist) return;
                customerDatalist.innerHTML = '';
                customerCache = {};
                rows.forEach(function (c) {
                    if (!c || !c.name) return;
                    customerCache[String(c.name).trim().toLowerCase()] = c;
                    var opt = document.createElement('option');
                    opt.value = c.name;
                    customerDatalist.appendChild(opt);
                });
            }

            function applyCustomerIfNameMatched() {
                if (!customerName) return;
                var key = String(customerName.value || '').trim().toLowerCase();
                var c = customerCache[key];
                if (!c) return;
                if (customerTaxId && (!customerTaxId.value || !String(customerTaxId.value).trim())) customerTaxId.value = c.tax_id || '';
                if (customerPhone && (!customerPhone.value || !String(customerPhone.value).trim())) customerPhone.value = c.phone || '';
                if (customerEmail && (!customerEmail.value || !String(customerEmail.value).trim())) customerEmail.value = c.email || '';
                if (customerAddress && (!customerAddress.value || !String(customerAddress.value).trim())) {
                    customerAddress.value = (c.address || c.tax_address || '').trim();
                }
            }

            if (customerName) {
                customerName.addEventListener('input', function () {
                    var q = String(customerName.value || '').trim();
                    if (nameTimer) clearTimeout(nameTimer);
                    if (q.length < 2) return;
                    nameTimer = setTimeout(async function () {
                        var rows = await lookupCustomers(q);
                        renderCustomerDatalist(rows);
                    }, 280);
                });
                customerName.addEventListener('change', applyCustomerIfNameMatched);
            }

            function normalizeTaxDigits(s) {
                return String(s || '').replace(/\D/g, '');
            }

            function isTaxCodeLikeQuery(q) {
                var compact = String(q || '').replace(/\s+/g, '');
                return /^[\d\-]{8,}$/.test(compact);
            }

            function pickCustomerRowForTax(rows, typedDigits) {
                if (!rows || !rows.length) return null;
                var exact = rows.find(function (r) { return normalizeTaxDigits(r.tax_id || '') === typedDigits; });
                return exact || rows[0];
            }

            function applyCustomerFromRow(c) {
                if (!c) return;
                if (customerName && c.name) customerName.value = c.name;
                if (customerTaxId && c.tax_id) customerTaxId.value = c.tax_id;
                if (customerPhone && c.phone) customerPhone.value = c.phone;
                if (customerEmail && c.email) customerEmail.value = c.email;
                var addr = (c.address || c.tax_address || '').trim();
                if (customerAddress && addr) customerAddress.value = addr;
            }

            if (customerTaxId) {
                customerTaxId.addEventListener('input', function () {
                    var q = String(customerTaxId.value || '').trim();
                    if (taxTimer) clearTimeout(taxTimer);
                    if (!isTaxCodeLikeQuery(q)) return;
                    taxTimer = setTimeout(async function () {
                        var rows = await lookupCustomers(q);
                        var c = pickCustomerRowForTax(rows, normalizeTaxDigits(q));
                        if (c) applyCustomerFromRow(c);
                    }, 320);
                });
                customerTaxId.addEventListener('change', async function () {
                    var q = String(customerTaxId.value || '').trim();
                    if (!isTaxCodeLikeQuery(q)) return;
                    var rows = await lookupCustomers(q);
                    var c = pickCustomerRowForTax(rows, normalizeTaxDigits(q));
                    if (c) applyCustomerFromRow(c);
                });
            }
        });
    </script>
@endsection 