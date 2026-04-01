@extends('layouts.admin')

@section('title', 'Thêm bảo hành mới')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-plus-circle"></i> Thêm bảo hành mới
            </h1>
            <p class="text-muted">Tạo thông tin bảo hành cho sản phẩm</p>
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

                    <form action="{{ route('admin.warranties.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Số seri (SN) *</label>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Chế độ nhập seri">
                                        <input type="radio" class="btn-check" name="serial_mode" id="serialModeSingle" value="single" autocomplete="off" checked>
                                        <label class="btn btn-outline-primary" for="serialModeSingle">Nhập 1 seri</label>
                                        <input type="radio" class="btn-check" name="serial_mode" id="serialModeBulk" value="bulk" autocomplete="off" {{ old('serial_numbers') ? 'checked' : '' }}>
                                        <label class="btn btn-outline-primary" for="serialModeBulk">Nhập nhiều seri</label>
                                    </div>
                                </div>

                                <div class="mb-3" id="serialSingleWrap">
                                    <input type="text" 
                                           class="form-control @error('serial_number') is-invalid @enderror" 
                                           id="serial_number" 
                                           name="serial_number" 
                                           value="{{ old('serial_number') }}" 
                                           placeholder="Ví dụ: SN123456789">
                                    <small class="text-info">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Số seri sẽ được tự động điền khi chọn sản phẩm có sẵn, bạn có thể sửa đổi nếu cần.
                                    </small>
                                    @error('serial_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3" id="serialBulkWrap" style="display:none;">
                                    <textarea
                                        class="form-control @error('serial_numbers') is-invalid @enderror"
                                        id="serial_numbers"
                                        name="serial_numbers"
                                        rows="6"
                                        placeholder="Dán nhiều số seri, mỗi dòng 1 seri (hoặc ngăn cách bằng dấu phẩy).">{{ old('serial_numbers') }}</textarea>
                                    <small class="text-muted">
                                        Nhập nhiều seri: mỗi dòng 1 seri, hoặc ngăn cách bằng dấu phẩy/dấu chấm phẩy.
                                    </small>
                                    @error('serial_numbers')
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
                                           value="{{ old('model_name') }}"
                                           placeholder="Ví dụ: K50PRO, K20PRO...">
                                    <small class="text-muted">Gõ tự do tên sản phẩm (model), không cần chọn sẵn.</small>
                                    @error('model_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                var singleRadio = document.getElementById('serialModeSingle');
                                var bulkRadio = document.getElementById('serialModeBulk');
                                var singleWrap = document.getElementById('serialSingleWrap');
                                var bulkWrap = document.getElementById('serialBulkWrap');
                                var serialInput = document.getElementById('serial_number');
                                var serialTextarea = document.getElementById('serial_numbers');

                                function applyMode(mode) {
                                    var isBulk = mode === 'bulk';
                                    if (singleWrap) singleWrap.style.display = isBulk ? 'none' : '';
                                    if (bulkWrap) bulkWrap.style.display = isBulk ? '' : 'none';
                                    if (serialInput) serialInput.required = !isBulk;
                                    if (serialTextarea) serialTextarea.required = isBulk;
                                }

                                if (singleRadio) singleRadio.addEventListener('change', function () { applyMode('single'); });
                                if (bulkRadio) bulkRadio.addEventListener('change', function () { applyMode('bulk'); });

                                var initialMode = (bulkRadio && bulkRadio.checked) ? 'bulk' : 'single';
                                applyMode(initialMode);
                            });
                        </script>

                        <div class="mb-3">
                            <label for="customer_tax_id" class="form-label fw-bold">Mã số thuế</label>
                            <input type="text"
                                   class="form-control @error('customer_tax_id') is-invalid @enderror"
                                   id="customer_tax_id"
                                   name="customer_tax_id"
                                   value="{{ old('customer_tax_id') }}"
                                   placeholder="Không bắt buộc">
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
                                           value="{{ old('customer_name') }}"
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
                                           value="{{ old('customer_phone') }}">
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
                                   value="{{ old('customer_email') }}">
                            @error('customer_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="customer_address" class="form-label fw-bold">Địa chỉ</label>
                            <textarea class="form-control @error('customer_address') is-invalid @enderror" 
                                      id="customer_address" 
                                      name="customer_address" 
                                      rows="2">{{ old('customer_address') }}</textarea>
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
                                           value="{{ old('stock_in_date') }}"
                                           max="{{ now()->toDateString() }}">
                                    <small class="text-muted">Tuỳ chọn.</small>
                                    @error('stock_in_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-6 col-xl-3">
                                <div class="mb-3">
                                    <label for="purchase_date" class="form-label fw-bold">Ngày bán *</label>
                                    <input type="date" 
                                           class="form-control @error('purchase_date') is-invalid @enderror" 
                                           id="purchase_date" 
                                           name="purchase_date" 
                                           value="{{ old('purchase_date') }}"
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
                                           value="{{ old('warranty_start_date') }}"
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
                                           value="{{ old('warranty_period_months', 12) }}" 
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
                                   value="{{ old('invoice_number') }}"
                                   placeholder="Để trống để tự động sinh mã">
                            <small class="text-muted">Để trống để hệ thống tự động sinh mã hóa đơn</small>
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
                                      rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Tạo bảo hành
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
                    <h6 class="m-0 font-weight-bold text-primary">Hướng dẫn</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold text-primary">
                            <i class="bi bi-info-circle"></i> Lưu ý quan trọng
                        </h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check text-success me-2"></i>
                                Số seri phải là duy nhất
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check text-success me-2"></i>
                                Chọn ngày bán xong, ngày bắt đầu bảo hành tự theo ngày bán (có thể sửa)
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check text-success me-2"></i>
                                Thời hạn bảo hành: 1-60 tháng
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check text-success me-2"></i>
                                Hệ thống tự tính ngày kết thúc
                            </li>
                        </ul>
                    </div>

                    <div class="alert alert-info">
                        <h6 class="fw-bold">
                            <i class="bi bi-lightbulb"></i> Mẹo
                        </h6>
                        <p class="mb-0">
                            Sau khi tạo bảo hành, khách hàng có thể tra cứu online bằng số seri để xem thông tin và tạo yêu cầu bảo hành.
                        </p>
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