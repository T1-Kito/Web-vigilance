@extends('layouts.admin')

@section('title', 'Kho giá đối thủ')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Kho giá đối thủ</h4>
        <div class="d-flex gap-2">
            @if(\App\Support\Permission::allows(auth()->user(), 'products.competitor.edit'))
                <form method="POST" action="{{ route('admin.products.competitor-prices.vinh-nguyen.sync') }}" onsubmit="return confirm('Quét lại giá từ Vinh Nguyễn?')">
                    @csrf
                    <button class="btn btn-primary btn-sm" type="submit">Quét Vinh Nguyễn</button>
                </form>
            @endif
            @if(\App\Support\Permission::allows(auth()->user(), 'products.view'))
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">Về sản phẩm</a>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.products.competitor-prices') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Tìm theo tên/key/sàn</label>
                    <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Ví dụ: k21 pro, sieuthivienthong">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Đối thủ</label>
                    <select name="competitor" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @foreach($competitors as $c)
                            <option value="{{ $c }}" @selected(request('competitor') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Lọc</button>
                    <a href="{{ route('admin.products.competitor-prices') }}" class="btn btn-light border">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 120px;">ID</th>
                            <th>Đối thủ</th>
                            <th>Product key</th>
                            <th>Tên lấy được</th>
                            <th class="text-end">Giá</th>
                            <th>Link</th>
                            <th>Thời điểm check</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($items as $row)
                        <tr>
                            <td>#{{ $row->id }}</td>
                            <td>{{ $row->competitor_name }}</td>
                            <td><code>{{ $row->product_key }}</code></td>
                            <td>{{ $row->product_name_raw }}</td>
                            <td class="text-end fw-semibold">
                                @if((float) $row->price > 0)
                                    {{ number_format((float) $row->price, 0, ',', '.') }}đ
                                @else
                                    <span class="text-muted">Web không để giá</span>
                                @endif
                            </td>
                            <td>
                                @if(!empty($row->product_url))
                                    <a href="{{ $row->product_url }}" target="_blank" rel="noopener" class="small">Mở link</a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>{{ optional($row->checked_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu kho giá đối thủ.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
