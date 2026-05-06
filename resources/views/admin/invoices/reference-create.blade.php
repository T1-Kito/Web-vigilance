@extends('layouts.admin')

@section('title', ($type === 'replacement' ? 'Thay thế hóa đơn' : 'Điều chỉnh hóa đơn'))

@section('content')
@php
    $sellerName = (string) (config('services.meinvoice.company_name') ?: config('app.name'));
    $sellerTaxCode = (string) (config('services.meinvoice.company_tax_code') ?: '');
    $sellerAddress = (string) (config('services.meinvoice.company_address') ?: '');
    $isReplacement = $type === 'replacement';
    $orgNo = $invoice->misa_invoice_code ?: $invoice->invoice_code;
    $orgSeries = $invoice->misa_inv_series ?: '—';
    $orgDate = optional($invoice->issued_at)->timezone(config('app.timezone'))->format('d/m/Y');
@endphp
<style>
.invoice-page-wrap{background:#eef1f5;min-height:calc(100vh - 56px)}
.invoice-sheet{max-width:1320px;width:96vw;margin:0 auto;background:#fff;border:1px solid #dbe1ea;border-radius:8px;box-shadow:0 16px 40px rgba(15,23,42,.1)}
.field-line{border:0;border-bottom:1px dotted #94a3b8;border-radius:0;background:transparent}
.field-line:focus{box-shadow:none;border-bottom-color:#2563eb}
.tbl th{background:#f8fafc;font-size:.84rem}
.tbl td,.tbl th{vertical-align:middle}
.mono{font-variant-numeric:tabular-nums}
</style>

<div class="invoice-page-wrap py-4">
<div class="container-fluid" style="max-width:1200px">
<div class="invoice-sheet p-4">
    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-1">Vui lòng kiểm tra lại các trường bị lỗi:</div>
            <ul class="mb-0 ps-3">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul>
        </div>
    @endif
    @if(session('misa_preview_url'))
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <span>Đã tạo bản nháp xem trước.</span>
            <a href="{{ session('misa_preview_url') }}" target="_blank" class="btn btn-sm btn-primary">Mở PDF nháp</a>
        </div>
    @endif

    <div class="text-center mb-3">
        <div class="fw-bold" style="font-size:1.35rem">HÓA ĐƠN GIÁ TRỊ GIA TĂNG</div>
        <div class="text-muted">Ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}</div>
    </div>

    <form method="POST" action="{{ route('admin.invoices.reference.store', [$invoice, $type]) }}" id="ref-form">
        @csrf

        <div class="row g-3 mb-3">
            <div class="col-md-8">
                <div class="text-muted small">&nbsp;</div>
            </div>
            <div class="col-md-4">
                <div class="border rounded p-2 bg-light"><strong>Ký hiệu:</strong> {{ $orgSeries }}</div>
                <div class="border rounded p-2 bg-light mt-2"><strong>Số:</strong> {{ $orgNo }}</div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-4"><label>Tên đơn vị mua</label><input name="buyer_legal_name" class="form-control field-line" value="{{ old('buyer_legal_name', $invoice->salesOrder->invoice_company_name ?? $invoice->salesOrder->receiver_name ?? '') }}"></div>
            <div class="col-md-4"><label>Mã số thuế</label><input name="buyer_tax_code" class="form-control field-line" value="{{ old('buyer_tax_code', $invoice->salesOrder->customer_tax_code ?? '') }}"></div>
            <div class="col-md-4"><label>Hình thức thanh toán</label><input name="payment_method_name" class="form-control field-line" value="{{ old('payment_method_name','TM/CK') }}"></div>
            <div class="col-md-7"><label>Địa chỉ</label><input name="buyer_address" class="form-control field-line" value="{{ old('buyer_address', $invoice->salesOrder->invoice_address ?? $invoice->salesOrder->receiver_address ?? '') }}"></div>
            <div class="col-md-5"><label>Email nhận HĐ</label><input name="receiver_email" class="form-control field-line" value="{{ old('receiver_email', $invoice->salesOrder->customer_email ?? '') }}"></div>
        </div>

        <div class="mt-3 p-2 border rounded bg-light">
            <strong>{{ $isReplacement ? 'Thay thế' : 'Điều chỉnh' }}</strong> cho hóa đơn số <strong>{{ $orgNo }}</strong>, ký hiệu <strong>{{ $orgSeries }}</strong>, ngày <strong>{{ $orgDate }}</strong>.
        </div>

        @unless($isReplacement)
        <div class="mt-3">
            <label class="fw-bold text-danger">Lý do điều chỉnh *</label>
            <textarea name="reason" class="form-control border-danger" rows="2" required>{{ old('reason') }}</textarea>
        </div>
        @else
            <input type="hidden" name="reason" value="{{ old('reason', 'Thay thế hóa đơn theo nghiệp vụ') }}">
        @endunless

        <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
            <strong>Danh sách hàng hóa (bắt buộc chọn từ sản phẩm)</strong>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-row">+ Thêm dòng</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered tbl" id="line-items-table">
                <thead><tr><th>#</th><th style="width:280px">Sản phẩm</th><th>Tên hàng hóa</th><th>ĐVT</th><th>SL</th><th>Đơn giá</th><th>VAT%</th><th>Thành tiền</th><th></th></tr></thead>
                <tbody>
                @php $lines = old('line_items') ?: $invoice->items->map(fn($r)=>['product_id'=>$r->product_id,'item_code'=>$r->product->serial_number??'','item_name'=>$r->product->name??'Sản phẩm','description'=>$r->product->information??$r->product->description??'','unit'=>$r->unit?:($r->product->unit_name??'Cái'),'quantity'=>(float)$r->quantity,'unit_price'=>(float)$r->unit_price,'vat_percent'=>(float)($r->product->vat_percent??$invoice->vat_percent??8)])->values()->all(); @endphp
                @foreach($lines as $i => $line)
                <tr>
                    <td class="text-center row-no">{{ $i+1 }}</td>
                    <td style="position:relative"><input type="hidden" name="line_items[{{ $i }}][product_id]" class="js-product-id" value="{{ $line['product_id'] ?? '' }}" required><input type="hidden" name="line_items[{{ $i }}][item_code]" class="js-item-code" value="{{ $line['item_code'] ?? '' }}"><input type="hidden" name="line_items[{{ $i }}][description]" class="js-description" value="{{ $line['description'] ?? '' }}"><input class="form-control js-product-search" value="{{ trim(($line['item_code'] ?? '').' - '.($line['item_name'] ?? '')) }}" placeholder="Tìm sản phẩm..." autocomplete="off" required><div class="list-group position-absolute js-suggest" style="z-index:2000;max-height:220px;overflow:auto;display:none"></div></td>
                    <td><input name="line_items[{{ $i }}][item_name]" class="form-control js-item-name" value="{{ $line['item_name'] ?? '' }}" readonly required></td>
                    <td><input name="line_items[{{ $i }}][unit]" class="form-control js-unit" value="{{ $line['unit'] ?? 'Cái' }}"></td>
                    <td><input type="number" step="0.0001" name="line_items[{{ $i }}][quantity]" class="form-control js-qty" value="{{ $line['quantity'] ?? 1 }}" required></td>
                    <td><input type="number" step="1" name="line_items[{{ $i }}][unit_price]" class="form-control js-price" value="{{ $line['unit_price'] ?? 0 }}" required></td>
                    <td><input type="number" step="0.01" name="line_items[{{ $i }}][vat_percent]" class="form-control js-vat" value="{{ $line['vat_percent'] ?? 8 }}"></td>
                    <td class="text-end mono js-line-total">0</td>
                    <td><button type="button" class="btn btn-sm btn-outline-danger js-remove-row">×</button></td>
                </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="7" class="text-end fw-bold">Tổng tiền hàng</td><td class="text-end fw-bold mono" id="sum-sub">0</td><td></td></tr>
                    <tr><td colspan="7" class="text-end fw-bold">Tổng tiền thuế</td><td class="text-end fw-bold mono" id="sum-vat">0</td><td></td></tr>
                    <tr><td colspan="7" class="text-end fw-bold text-danger">Tổng thanh toán</td><td class="text-end fw-bold text-danger mono" id="sum-total">0</td><td></td></tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">{{ $isReplacement ? 'Phát hành thay thế' : 'Phát hành điều chỉnh' }}</button>
            <button type="submit" name="preview_mode" value="view" formaction="{{ route('admin.invoices.reference.preview', [$invoice, $type]) }}" formmethod="POST" class="btn btn-outline-secondary">Xem nháp</button>
            <button type="submit" name="preview_mode" value="save" formaction="{{ route('admin.invoices.reference.store', [$invoice, $type]) }}" formmethod="POST" class="btn btn-outline-dark">Lưu nháp</button>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-light border">Quay lại</a>
        </div>
    </form>
</div></div></div>

<script>
(function(){const t=document.getElementById('line-items-table'),b=t.querySelector('tbody'),u=@json(route('admin.products.lookup'));const f=n=>new Intl.NumberFormat('vi-VN').format(Math.round(n||0));function re(){let s=0,v=0;b.querySelectorAll('tr').forEach((r,i)=>{r.querySelector('.row-no').textContent=i+1;r.querySelectorAll('input[name^="line_items["]').forEach(x=>x.name=x.name.replace(/line_items\[\d+\]/,'line_items['+i+']'));const q=parseFloat(r.querySelector('.js-qty')?.value||0),p=parseFloat(r.querySelector('.js-price')?.value||0),vr=parseFloat(r.querySelector('.js-vat')?.value||0),ln=q*p,lv=ln*(vr/100);s+=ln;v+=lv;r.querySelector('.js-line-total').textContent=f(ln)});document.getElementById('sum-sub').textContent=f(s);document.getElementById('sum-vat').textContent=f(v);document.getElementById('sum-total').textContent=f(s+v)}function lk(r){const i=r.querySelector('.js-product-search'),sg=r.querySelector('.js-suggest');let tm=null;i.addEventListener('input',()=>{r.querySelector('.js-product-id').value='';r.querySelector('.js-item-name').value='';clearTimeout(tm);const q=i.value.trim();if(q.length<2){sg.style.display='none';return;}tm=setTimeout(async()=>{const rs=await fetch(u+'?q='+encodeURIComponent(q));const d=await rs.json();sg.innerHTML='';(d||[]).forEach(it=>{const a=document.createElement('button');a.type='button';a.className='list-group-item list-group-item-action';a.textContent=(it.serial_number?it.serial_number+' - ':'')+it.name;a.onclick=()=>{r.querySelector('.js-product-id').value=it.id||'';r.querySelector('.js-item-code').value=it.serial_number||'';r.querySelector('.js-item-name').value=it.name||'';r.querySelector('.js-price').value=it.final_price||it.price||0;i.value=a.textContent;sg.style.display='none';re()};sg.appendChild(a)});sg.style.display=d&&d.length?'block':'none'},220)});document.addEventListener('click',e=>{if(!r.contains(e.target))sg.style.display='none'})}
document.getElementById('btn-add-row').onclick=()=>{const i=b.querySelectorAll('tr').length,tr=document.createElement('tr');tr.innerHTML=`<td class="text-center row-no">${i+1}</td><td style="position:relative"><input type="hidden" name="line_items[${i}][product_id]" class="js-product-id" required><input type="hidden" name="line_items[${i}][item_code]" class="js-item-code"><input type="hidden" name="line_items[${i}][description]" class="js-description"><input class="form-control js-product-search" placeholder="Tìm sản phẩm..." autocomplete="off" required><div class="list-group position-absolute js-suggest" style="z-index:2000;max-height:220px;overflow:auto;display:none"></div></td><td><input name="line_items[${i}][item_name]" class="form-control js-item-name" readonly required></td><td><input name="line_items[${i}][unit]" class="form-control js-unit" value="Cái"></td><td><input type="number" step="0.0001" name="line_items[${i}][quantity]" class="form-control js-qty" value="1" required></td><td><input type="number" step="1" name="line_items[${i}][unit_price]" class="form-control js-price" value="0" required></td><td><input type="number" step="0.01" name="line_items[${i}][vat_percent]" class="form-control js-vat" value="8"></td><td class="text-end mono js-line-total">0</td><td><button type="button" class="btn btn-sm btn-outline-danger js-remove-row">×</button></td>`;b.appendChild(tr);lk(tr);re()};
b.addEventListener('click',e=>{if(e.target.closest('.js-remove-row')){if(b.querySelectorAll('tr').length<=1)return;e.target.closest('tr').remove();re()}});b.addEventListener('input',e=>{if(e.target.matches('.js-qty,.js-price,.js-vat'))re()});[...b.querySelectorAll('tr')].forEach(lk);re();document.getElementById('ref-form').addEventListener('submit',e=>{if([...b.querySelectorAll('.js-product-id')].some(x=>!x.value)){e.preventDefault();alert('Vui lòng chọn sản phẩm từ danh mục cho tất cả các dòng hàng hóa.')}})})();
</script>
@endsection