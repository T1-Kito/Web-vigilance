@extends('layouts.admin')

@section('title', 'Sửa phiếu mượn hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4" style="display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap;">
        <h2 class="mb-0">Sửa phiếu mượn hàng</h2>
        <div style="display:flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.borrow-requests.show', $borrowRequest) }}" class="btn btn-outline-primary">Xem</a>
            <a href="{{ route('admin.borrow-requests.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Có lỗi xảy ra!</h5>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('admin.borrow-requests.update', $borrowRequest) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Người đề nghị</label>
                        <input type="text" name="requested_by_name" class="form-control" value="{{ old('requested_by_name', $borrowRequest->requested_by_name) }}" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Khách hàng</label>
                        <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $borrowRequest->customer_name) }}" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Mục đích</label>
                        <input type="text" name="purpose" class="form-control" value="{{ old('purpose', $borrowRequest->purpose) }}" maxlength="255">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Công trình hiện tại</label>
                        <input type="text" name="current_project" class="form-control" value="{{ old('current_project', $borrowRequest->current_project) }}" maxlength="255">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Thời gian mượn từ</label>
                        <input type="date" name="borrow_from" class="form-control" value="{{ old('borrow_from', optional($borrowRequest->borrow_from)->format('Y-m-d')) }}" id="borrowFrom">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Đến</label>
                        <input type="date" name="borrow_to" class="form-control" value="{{ old('borrow_to', optional($borrowRequest->borrow_to)->format('Y-m-d')) }}" id="borrowTo">
                        <small class="text-muted">Mặc định +7 ngày nếu để trống</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Trạng thái</label>
                        <select name="status" class="form-select" required>
                            @foreach($statusOptions as $k => $label)
                                <option value="{{ $k }}" {{ old('status', $borrowRequest->status)===$k ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Đề xuất (cọc)</label>
                        @php $dep = old('deposit_text', $borrowRequest->deposit_text ?: 'Không cọc'); @endphp
                        <div class="d-flex align-items-center gap-3" style="min-height: 38px;">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="deposit_text" id="depositNone" value="Không cọc" {{ $dep==='Không cọc' ? 'checked' : '' }}>
                                <label class="form-check-label" for="depositNone">Không cọc</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="deposit_text" id="depositYes" value="Có cọc" {{ $dep==='Có cọc' ? 'checked' : '' }}>
                                <label class="form-check-label" for="depositYes">Có cọc</label>
                            </div>
                        </div>
                    </div>

                    @php
                        $depAmount = old('deposit_amount', $borrowRequest->deposit_amount);
                        $depAmountVal = $depAmount !== null && $depAmount !== '' ? rtrim(rtrim(number_format((float) $depAmount, 2, '.', ''), '0'), '.') : '';
                    @endphp
                    <div class="col-md-3" id="depositAmountWrap" style="display:none;">
                        <label class="form-label fw-bold">Số tiền cọc</label>
                        <input type="number" step="0.01" min="0" name="deposit_amount" class="form-control" value="{{ $depAmountVal }}" placeholder="Nhập số tiền">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Ký duyệt (tên)</label>
                        <input type="text" name="approved_by_name" class="form-control" value="{{ old('approved_by_name', $borrowRequest->approved_by_name) }}" maxlength="255">
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex align-items-center justify-content-between mb-2" style="gap: 12px; flex-wrap: wrap;">
                    <div class="fw-bold" style="font-size:1.05rem;">Danh sách hàng mượn</div>
                    <button type="button" class="btn btn-outline-primary" id="addItemRow"><i class="bi bi-plus-circle"></i> Thêm dòng</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead>
                            <tr>
                                <th style="width:6%; text-align:center;">STT</th>
                                <th style="width:30%;">Tên hàng</th>
                                <th style="width:12%;">ĐVT</th>
                                <th style="width:12%;">Số lượng</th>
                                <th style="width:16%;">Giá trị</th>
                                <th style="width:18%;">Ghi chú</th>
                                <th style="width:6%;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $oldItems = old('items'); @endphp
                            @if(is_array($oldItems) && count($oldItems) > 0)
                                @foreach($oldItems as $i => $it)
                                    <tr>
                                        <td class="text-center stt"></td>
                                        <td><input type="text" name="items[{{ $i }}][item_name]" class="form-control" value="{{ $it['item_name'] ?? '' }}" maxlength="255"></td>
                                        <td><input type="text" name="items[{{ $i }}][unit]" class="form-control" value="{{ $it['unit'] ?? '' }}" maxlength="50"></td>
                                        <td><input type="number" step="0.01" name="items[{{ $i }}][quantity]" class="form-control" value="{{ $it['quantity'] ?? '' }}"></td>
                                        <td><input type="number" step="0.01" name="items[{{ $i }}][value]" class="form-control" value="{{ $it['value'] ?? '' }}"></td>
                                        <td><input type="text" name="items[{{ $i }}][note]" class="form-control" value="{{ $it['note'] ?? '' }}" maxlength="255"></td>
                                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                @endforeach
                            @else
                                @foreach($borrowRequest->items as $i => $it)
                                    <tr>
                                        <td class="text-center stt"></td>
                                        <td><input type="text" name="items[{{ $i }}][item_name]" class="form-control" value="{{ $it->item_name }}" maxlength="255"></td>
                                        <td><input type="text" name="items[{{ $i }}][unit]" class="form-control" value="{{ $it->unit }}" maxlength="50"></td>
                                        <td><input type="number" step="0.01" name="items[{{ $i }}][quantity]" class="form-control" value="{{ $it->quantity }}"></td>
                                        <td><input type="number" step="0.01" name="items[{{ $i }}][value]" class="form-control" value="{{ $it->value }}"></td>
                                        <td><input type="text" name="items[{{ $i }}][note]" class="form-control" value="{{ $it->note }}" maxlength="255"></td>
                                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                @endforeach
                                @if($borrowRequest->items->count() === 0)
                                    <tr>
                                        <td class="text-center stt"></td>
                                        <td><input type="text" name="items[0][item_name]" class="form-control" maxlength="255"></td>
                                        <td><input type="text" name="items[0][unit]" class="form-control" maxlength="50"></td>
                                        <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control"></td>
                                        <td><input type="number" step="0.01" name="items[0][value]" class="form-control"></td>
                                        <td><input type="text" name="items[0][note]" class="form-control" maxlength="255"></td>
                                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-x"></i></button></td>
                                    </tr>
                                @endif
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableBody = document.querySelector('#itemsTable tbody');
    const addBtn = document.getElementById('addItemRow');
    const borrowFrom = document.getElementById('borrowFrom');
    const borrowTo = document.getElementById('borrowTo');
    const depositAmountWrap = document.getElementById('depositAmountWrap');

    function toggleDepositAmount() {
        if (!depositAmountWrap) return;
        const dep = document.querySelector('input[name="deposit_text"]:checked');
        const isYes = dep && dep.value === 'Có cọc';
        depositAmountWrap.style.display = isYes ? '' : 'none';
        if (!isYes) {
            const inp = depositAmountWrap.querySelector('input[name="deposit_amount"]');
            if (inp) inp.value = '';
        }
    }

    function pad(n){ return String(n).padStart(2,'0'); }

    function addDays(date, days) {
        const d = new Date(date.getTime());
        d.setDate(d.getDate() + days);
        return d;
    }

    function toYmd(date) {
        return date.getFullYear() + '-' + pad(date.getMonth()+1) + '-' + pad(date.getDate());
    }

    function reindex() {
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        rows.forEach((tr, idx) => {
            const stt = tr.querySelector('.stt');
            if (stt) stt.textContent = String(idx + 1);

            tr.querySelectorAll('input[name^="items["]').forEach(inp => {
                inp.name = inp.name.replace(/items\[\d+\]/, 'items[' + idx + ']');
            });
        });
    }

    function bindRemove() {
        tableBody.querySelectorAll('.remove-row').forEach(btn => {
            btn.onclick = function() {
                const tr = btn.closest('tr');
                if (tr) tr.remove();
                if (tableBody.querySelectorAll('tr').length === 0) {
                    const tr2 = document.createElement('tr');
                    tr2.innerHTML = `
                        <td class="text-center stt"></td>
                        <td><input type="text" name="items[0][item_name]" class="form-control" maxlength="255"></td>
                        <td><input type="text" name="items[0][unit]" class="form-control" maxlength="50"></td>
                        <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control"></td>
                        <td><input type="number" step="0.01" name="items[0][value]" class="form-control"></td>
                        <td><input type="text" name="items[0][note]" class="form-control" maxlength="255"></td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-x"></i></button></td>
                    `;
                    tableBody.appendChild(tr2);
                }
                reindex();
                bindRemove();
            };
        });
    }

    addBtn.addEventListener('click', function() {
        const idx = tableBody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-center stt"></td>
            <td><input type="text" name="items[${idx}][item_name]" class="form-control" maxlength="255"></td>
            <td><input type="text" name="items[${idx}][unit]" class="form-control" maxlength="50"></td>
            <td><input type="number" step="0.01" name="items[${idx}][quantity]" class="form-control"></td>
            <td><input type="number" step="0.01" name="items[${idx}][value]" class="form-control"></td>
            <td><input type="text" name="items[${idx}][note]" class="form-control" maxlength="255"></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-x"></i></button></td>
        `;
        tableBody.appendChild(tr);
        reindex();
        bindRemove();
    });

    function maybeFillBorrowTo() {
        if (!borrowFrom || !borrowTo) return;
        if (!borrowFrom.value) return;
        if (borrowTo.value) return;
        const from = new Date(borrowFrom.value + 'T00:00:00');
        const to = addDays(from, 7);
        borrowTo.value = toYmd(to);
    }

    if (borrowFrom) borrowFrom.addEventListener('change', maybeFillBorrowTo);

    document.querySelectorAll('input[name="deposit_text"]').forEach(r => {
        r.addEventListener('change', toggleDepositAmount);
    });

    reindex();
    bindRemove();
    maybeFillBorrowTo();
    toggleDepositAmount();
});
</script>
@endsection
