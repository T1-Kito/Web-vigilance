@extends('layouts.admin')

@section('title', 'Quản lý mẫu in')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1">Quản lý mẫu in chứng từ</h1>
            <div class="text-muted">Upload mẫu Word (.docx), bật/tắt và chọn mẫu mặc định theo từng loại chứng từ.</div>
        </div>
        <a href="{{ route('admin.document-templates.fields.download') }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-arrow-down me-1"></i>Tải danh sách trường trộn
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="white-space: pre-line;">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold">Thêm mẫu in mới</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.document-templates.store') }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Tên mẫu</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Loại chứng từ</label>
                    <select name="type" class="form-select" required>
                        <option value="quote">Báo giá</option>
                        <option value="sales_order">Đơn hàng</option>
                        <option value="invoice">Hóa đơn</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">File mẫu (.docx / .xlsx / .xls)</label>
                    <input type="file" name="file" class="form-control" accept=".docx,.xlsx,.xls" required>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit">Upload</button>
                </div>

                <div class="col-12 d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Kích hoạt</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="is_default" name="is_default">
                        <label class="form-check-label" for="is_default">Đặt làm mặc định</label>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-bold">Danh sách mẫu in</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên mẫu</th>
                            <th>Loại</th>
                            <th>Trạng thái</th>
                            <th>Mặc định</th>
                            <th>Ngày tạo</th>
                            <th>In thử</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($templates as $tpl)
                        <tr>
                            <td>{{ $tpl->id }}</td>
                            <td class="fw-semibold">{{ $tpl->name }}</td>
                            <td>
                                @if($tpl->type === 'quote') Báo giá
                                @elseif($tpl->type === 'sales_order') Đơn hàng
                                @else Hóa đơn
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $tpl->is_active ? 'success' : 'secondary' }}">{{ $tpl->is_active ? 'Kích hoạt' : 'Tắt' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $tpl->is_default ? 'primary' : 'light text-dark' }}">{{ $tpl->is_default ? 'Mặc định' : '---' }}</span>
                            </td>
                            <td>{{ optional($tpl->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($tpl->type === 'quote')
                                    @php $sampleQuote = \App\Models\Quote::query()->latest('id')->value('id'); @endphp
                                    @if($sampleQuote)
                                        <a href="{{ route('admin.document-templates.render.quote', ['documentTemplate' => $tpl, 'quote' => $sampleQuote]) }}" class="btn btn-sm btn-outline-primary">In thử BG mới nhất</a>
                                    @else
                                        <span class="text-muted small">Chưa có báo giá</span>
                                    @endif
                                @elseif($tpl->type === 'sales_order')
                                    @php $sampleSo = \App\Models\SalesOrder::query()->latest('id')->value('id'); @endphp
                                    @if($sampleSo)
                                        <a href="{{ route('admin.document-templates.render.sales-order', ['documentTemplate' => $tpl, 'salesOrder' => $sampleSo]) }}" class="btn btn-sm btn-outline-primary">In thử SO mới nhất</a>
                                    @else
                                        <span class="text-muted small">Chưa có đơn hàng</span>
                                    @endif
                                @else
                                    <span class="text-muted small">Chưa hỗ trợ</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#editTpl{{ $tpl->id }}">Sửa</button>
                                <form method="POST" action="{{ route('admin.document-templates.destroy', $tpl) }}" class="d-inline" onsubmit="return confirm('Xóa mẫu này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="editTpl{{ $tpl->id }}">
                            <td colspan="8" class="bg-light">
                                <form method="POST" action="{{ route('admin.document-templates.update', $tpl) }}" enctype="multipart/form-data" class="row g-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="col-md-3"><input name="name" class="form-control form-control-sm" value="{{ $tpl->name }}" required></div>
                                    <div class="col-md-2">
                                        <select name="type" class="form-select form-select-sm" required>
                                            <option value="quote" @selected($tpl->type === 'quote')>Báo giá</option>
                                            <option value="sales_order" @selected($tpl->type === 'sales_order')>Đơn hàng</option>
                                            <option value="invoice" @selected($tpl->type === 'invoice')>Hóa đơn</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3"><input type="file" name="file" class="form-control form-control-sm" accept=".docx,.xlsx,.xls"></div>
                                    <div class="col-md-3 d-flex align-items-center gap-3">
                                        <label class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($tpl->is_active)>
                                            <span class="form-check-label small">Kích hoạt</span>
                                        </label>
                                        <label class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_default" value="1" @checked($tpl->is_default)>
                                            <span class="form-check-label small">Mặc định</span>
                                        </label>
                                    </div>
                                    <div class="col-md-1 d-grid">
                                        <button class="btn btn-sm btn-primary" type="submit">Lưu</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Chưa có mẫu in nào.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $templates->links('pagination::bootstrap-5') }}</div>
    </div>

    <div class="alert alert-info mt-4 mb-0">
        <b>Cách dùng placeholder trong file Word:</b>
        <div class="small mt-1">
            Ví dụ: <code>@{{CustomerName}}</code>, <code>@{{QuoteCode}}</code>, <code>@{{TotalAmount}}</code>.<br>
            Dòng sản phẩm: dùng block <code>@{{#Items}}</code> ... <code>@{{/Items}}</code> với các trường con như <code>@{{Item.Name}}</code>, <code>@{{Item.Quantity}}</code>.
        </div>
    </div>
</div>
@endsection
