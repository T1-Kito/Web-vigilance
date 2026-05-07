@extends('layouts.admin')

@section('title', 'Danh sách mẫu hóa đơn MISA')

@section('content')
<div class="container-fluid py-4">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <h1 class="h4 fw-bold mb-1">Danh sách mẫu hóa đơn từ MISA</h1>
            <div class="text-muted">Nguồn dữ liệu: <code>/invoice/templates</code> (invoiceWithCode=true/false, ticket=false).</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.misa-meinvoice.settings.edit') }}" class="btn btn-outline-secondary">Về cấu hình</a>
            <a href="{{ route('admin.misa-meinvoice.settings.invoice-templates') }}" class="btn btn-primary">Tải lại danh sách</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Trạng thái API</div>
                    <div class="fw-semibold">{{ $result['status'] ?? '---' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Successful</div>
                    <div class="fw-semibold">{{ !empty($result['successful']) ? 'true' : 'false' }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Số mẫu</div>
                    <div class="fw-semibold">{{ count((array) $templates) }}</div>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">Mẫu đang chọn mặc định</div>
                    <div class="fw-semibold">{{ $settings['invoice_template_id'] ?? '---' }}</div>
                    <div class="small text-muted">Ký hiệu: {{ $settings['inv_series'] ?? '---' }}</div>
                </div>
            </div>
            @if(!empty($result['error']))
                <div class="alert alert-danger mt-3 mb-0">{{ $result['error'] }}</div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Tên mẫu</th>
                            <th>Ký hiệu</th>
                            <th>Mẫu số</th>
                            <th>Sử dụng</th>
                            <th>InvoiceTemplateID</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($templates as $item)
                        @php($templateId = (string) (data_get($item, 'IPTemplateID', data_get($item, 'ipTemplateID', data_get($item, 'InvoiceTemplateID', data_get($item, 'invoiceTemplateID', ''))))))
                        @php($name = (string) (data_get($item, 'TemplateName', data_get($item, 'templateName', data_get($item, 'InvTemplateName', data_get($item, 'invTemplateName', ''))))))
                        @php($seriesRaw = (string) (data_get($item, 'InvSeries', data_get($item, 'invSeries', data_get($item, 'OrgInvSeries', data_get($item, 'orgInvSeries', ''))))))
                        @php($templateNo = (string) (data_get($item, 'InvTemplateNo', data_get($item, 'invTemplateNo', data_get($item, 'FormNo', data_get($item, 'formNo', ''))))))
                        @php($series = strtoupper(trim($seriesRaw)))
                        @php($isUsedRaw = data_get($item, 'IsUsed', data_get($item, 'isUsed', null)))
                        @php($inactiveRaw = data_get($item, 'Inactive', data_get($item, 'inactive', null)))
                        @php($inactive = filter_var($inactiveRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))
                        @php($isUsed = filter_var($isUsedRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))
                        @php($previewUrl = $templateId ? "https://app3.meinvoice.vn/v3/template-view/{$templateId}" : '')
                        <tr>
                            <td>{{ $name !== '' ? $name : '---' }}</td>
                            <td><span class="fw-semibold">{{ $series !== '' ? $series : '---' }}</span></td>
                            <td>{{ $templateNo !== '' ? $templateNo : '---' }}</td>
                            <td>
                                @if($inactive === true)
                                    <span class="badge bg-secondary">Ngừng sử dụng</span>
                                @elseif($isUsed === true)
                                    <span class="badge bg-success">Đang sử dụng</span>
                                @elseif($isUsed === false)
                                    <span class="badge bg-secondary">Ngừng sử dụng</span>
                                @else
                                    <span class="badge bg-light text-dark">Không rõ</span>
                                @endif
                            </td>
                            <td><code>{{ $templateId }}</code></td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('admin.misa-meinvoice.settings.invoice-templates.default') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="invoice_template_id" value="{{ $templateId }}">
                                    <input type="hidden" name="inv_series" value="{{ $series }}">
                                    <input type="hidden" name="inv_template_no" value="{{ $templateNo }}">
                                    <input type="hidden" name="template_name" value="{{ $name }}">
                                    <input type="hidden" name="is_used" value="{{ $isUsed === true ? 1 : 0 }}">
                                    <button type="submit" class="btn btn-sm {{ ($settings['invoice_template_id'] ?? '') === $templateId ? 'btn-success' : 'btn-outline-primary' }}" {{ $templateId === '' || $series === '' || $inactive === true ? 'disabled' : '' }}>
                                        {{ ($settings['invoice_template_id'] ?? '') === $templateId ? 'Đang mặc định' : 'Chọn mặc định' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Không có dữ liệu mẫu hóa đơn.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
