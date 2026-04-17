@extends('layouts.admin')

@section('title', 'Quản lý PDF')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1">Quản lý PDF template</h1>
            <div class="text-muted">Tạo mẫu PDF bằng HTML/CSS, bật tắt mẫu active và xuất PDF giống layout Word.</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="white-space: pre-line;">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Tạo mẫu PDF mới</span>
            @php $latestQuoteId = \App\Models\Quote::query()->latest('id')->value('id'); @endphp
            @if($latestQuoteId)
                <a href="{{ route('admin.pdf-templates.render-default.quote', ['quote' => $latestQuoteId]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    Xem PDF mặc định mới nhất
                </a>
            @else
                <span class="btn btn-sm btn-outline-secondary disabled">Chưa có báo giá để xem</span>
            @endif
        </div>
        <div class="card-body">
            <div class="alert alert-light border mb-4">
                Trang này dùng để quản lý template PDF HTML/CSS riêng. Phần chỉnh HTML chi tiết nằm ở nút <b>Sửa</b> trong danh sách bên dưới.
            </div>

            <form method="POST" action="{{ route('admin.pdf-templates.store') }}" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label">Tên mẫu</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Loại</label>
                    <select name="type" class="form-select" required>
                        <option value="quote">Báo giá</option>
                        <option value="sales_order">Đơn hàng</option>
                        <option value="invoice">Hóa đơn</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">File mẫu</label>
                    <select name="view_name" class="form-select" required>
                        @foreach(($availableViews ?? ['preview' => 'preview.blade.php']) as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Kích hoạt</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="is_default" name="is_default">
                        <label class="form-check-label" for="is_default">Mặc định</label>
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Lưu mẫu PDF</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Danh sách mẫu PDF</span>
            <div class="small text-muted">Mẫu kích hoạt sẽ được dùng khi xuất PDF báo giá</div>
        </div>
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
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($templates as $tpl)
                        <tr>
                            <td>{{ $tpl->id }}</td>
                            <td class="fw-semibold">{{ $tpl->name }}</td>
                            <td>{{ $tpl->type }}</td>
                            <td>
                                <span class="badge bg-{{ $tpl->is_active ? 'success' : 'secondary' }}">{{ $tpl->is_active ? 'Kích hoạt' : 'Tắt' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $tpl->is_default ? 'primary' : 'light text-dark' }}">{{ $tpl->is_default ? 'Mặc định' : '---' }}</span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.pdf-templates.preview', $tpl) }}" target="_blank" class="btn btn-sm btn-outline-primary">Xem trước</a>
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#editPdfTpl{{ $tpl->id }}">Sửa</button>
                                <form method="POST" action="{{ route('admin.pdf-templates.clone', $tpl) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success">Nhân bản</button>
                                </form>
                                <form method="POST" action="{{ route('admin.pdf-templates.toggle-active', $tpl) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-warning">{{ $tpl->is_active ? 'Hủy kích hoạt' : 'Kích hoạt' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.pdf-templates.set-default', $tpl) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-info">Đặt mặc định</button>
                                </form>
                                <form method="POST" action="{{ route('admin.pdf-templates.destroy', $tpl) }}" class="d-inline" onsubmit="return confirm('Xóa mẫu PDF này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="editPdfTpl{{ $tpl->id }}">
                            <td colspan="6" class="bg-light">
                                <form method="POST" action="{{ route('admin.pdf-templates.update', $tpl) }}" class="row g-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="col-md-3">
                                        <input name="name" class="form-control form-control-sm" value="{{ $tpl->name }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="type" class="form-select form-select-sm" required>
                                            <option value="quote" @selected($tpl->type === 'quote')>Báo giá</option>
                                            <option value="sales_order" @selected($tpl->type === 'sales_order')>Đơn hàng</option>
                                            <option value="invoice" @selected($tpl->type === 'invoice')>Hóa đơn</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="view_name" class="form-select form-select-sm" required>
                                            <option value="preview" @selected(($tpl->view_name ?? 'preview') === 'preview')>preview.blade.php</option>
                                            <option value="2preview" @selected(($tpl->view_name ?? 'preview') === '2preview')>2preview.blade.php</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <textarea name="css_content" class="form-control form-control-sm" rows="2">{{ $tpl->css_content }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <textarea name="html_content" class="form-control form-control-sm font-monospace" rows="10" required>{{ $tpl->html_content }}</textarea>
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center gap-3">
                                        <label class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($tpl->is_active)>
                                            <span class="form-check-label small">Kích hoạt</span>
                                        </label>
                                        <label class="form-check mb-0">
                                            <input class="form-check-input" type="checkbox" name="is_default" value="1" @checked($tpl->is_default)>
                                            <span class="form-check-label small">Mặc định</span>
                                        </label>
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button class="btn btn-sm btn-primary" type="submit">Lưu</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Chưa có mẫu PDF nào.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small text-muted">{{ $templates->total() }} mẫu</div>
            <div>{{ $templates->links('pagination::bootstrap-5') }}</div>
        </div>
    </div>
</div>
@endsection
