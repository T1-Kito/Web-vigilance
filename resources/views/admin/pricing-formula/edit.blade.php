@extends('layouts.admin')

@section('title', 'Thiết lập công thức giá')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Thiết lập công thức giá</h4>
            <div class="text-muted small">Sếp có thể chỉnh công thức, hệ thống sẽ áp dụng cho sản phẩm lưu mới/cập nhật.</div>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Quay lại sản phẩm</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Công thức hiện hành</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.pricing-formula.update') }}" class="row g-3" id="pricing-formula-form">
                @csrf
                @method('PATCH')

                <div class="col-md-4">
                    <label class="form-label">Hệ số giá niêm yết (x giá vốn)</label>
                    <input type="number" step="0.01" min="0.01" name="list_multiplier" class="form-control" value="{{ old('list_multiplier', number_format((float) $setting->list_multiplier, 2, '.', '')) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">% giảm khách lẻ (từ giá niêm yết)</label>
                    <input type="number" step="0.01" min="0" max="100" name="retail_discount_percent" class="form-control" value="{{ old('retail_discount_percent', number_format((float) $setting->retail_discount_percent, 2, '.', '')) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">% tăng đại lý (SL 1-5)</label>
                    <input type="number" step="0.01" min="0" max="1000" name="agent_markup_1_5_percent" class="form-control" value="{{ old('agent_markup_1_5_percent', number_format((float) $setting->agent_markup_1_5_percent, 2, '.', '')) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">% tăng đại lý (SL 6-10)</label>
                    <input type="number" step="0.01" min="0" max="1000" name="agent_markup_6_10_percent" class="form-control" value="{{ old('agent_markup_6_10_percent', number_format((float) $setting->agent_markup_6_10_percent, 2, '.', '')) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">% tăng đại lý (SL &gt;10)</label>
                    <input type="number" step="0.01" min="0" max="1000" name="agent_markup_over_10_percent" class="form-control" value="{{ old('agent_markup_over_10_percent', number_format((float) $setting->agent_markup_over_10_percent, 2, '.', '')) }}" required>
                </div>

                <div class="col-12">
                    <div class="alert alert-info mb-0 small" id="formula-preview">
                        Preview: Giá niêm yết = Giá vốn x{{ number_format((float) $setting->list_multiplier, 2, '.', '') }}; Khách lẻ = Giá niêm yết -{{ number_format((float) $setting->retail_discount_percent, 2, '.', '') }}%; Đại lý: 1-5 (+{{ number_format((float) $setting->agent_markup_1_5_percent, 2, '.', '') }}%), 6-10 (+{{ number_format((float) $setting->agent_markup_6_10_percent, 2, '.', '') }}%), >10 (+{{ number_format((float) $setting->agent_markup_over_10_percent, 2, '.', '') }}%).
                    </div>
                </div>

                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Lưu công thức</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('pricing-formula-form');
    const preview = document.getElementById('formula-preview');
    if (!form || !preview) return;

    function num(name, fallback) {
        const el = form.querySelector(`[name="${name}"]`);
        const v = Number(el?.value || fallback || 0);
        return Number.isFinite(v) ? v : fallback;
    }

    function fmt(n) {
        const v = Number(n || 0);
        if (!Number.isFinite(v)) return '0';
        return Number.isInteger(v) ? String(v) : v.toFixed(2).replace(/\.00$/, '');
    }

    function render() {
        preview.textContent = `Preview: Giá niêm yết = Giá vốn x${fmt(num('list_multiplier', 2))}; `
            + `Khách lẻ = Giá niêm yết -${fmt(num('retail_discount_percent', 15))}%; `
            + `Đại lý: 1-5 (+${fmt(num('agent_markup_1_5_percent', 30))}%), `
            + `6-10 (+${fmt(num('agent_markup_6_10_percent', 25))}%), `
            + `>10 (+${fmt(num('agent_markup_over_10_percent', 15))}%).`;
    }

    form.querySelectorAll('input').forEach(function (el) {
        el.addEventListener('input', render);
    });

    render();
});
</script>
@endsection
