@extends('layouts.admin')

@section('title', 'Cấu hình MISA meInvoice')

@section('content')
<div class="container-fluid py-4">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Cấu hình MISA meInvoice</h1>
            <div class="text-muted">Lưu thông tin kết nối và ký số HSM cho luồng phát hành hóa đơn.</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.misa-meinvoice.settings.update') }}">
                @csrf
                @method('PATCH')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Base URL</label>
                        <input type="text" name="base_url" class="form-control" value="{{ old('base_url', $settings['base_url'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">App ID</label>
                        <input type="text" name="app_id" class="form-control" value="{{ old('app_id', $settings['app_id'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Mã số thuế</label>
                        <input type="text" name="tax_code" class="form-control" value="{{ old('tax_code', $settings['tax_code'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control" value="{{ old('username', $settings['username'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" value="{{ old('password', $settings['password'] ?? '') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Sign Type</label>
                        <select name="sign_type" class="form-select">
                            @foreach([1 => '1 - USB/File', 2 => '2 - HSM có hiển thị CKS', 3 => '3 - HSM bất đồng bộ', 4 => '4 - Vé không mã', 5 => '5 - Không ký', 6 => '6 - MTT bất đồng bộ'] as $key => $label)
                                <option value="{{ $key }}" @selected((string) old('sign_type', $settings['sign_type'] ?? 2) === (string) $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Certificate SN</label>
                        <input type="text" name="certificate_sn" list="misa-certificates" class="form-control" value="{{ old('certificate_sn', $settings['certificate_sn'] ?? '') }}" placeholder="Bấm 'Lấy chứng thư số' để chọn nhanh">
                        @if(session('misa_certificates.items'))
                            <datalist id="misa-certificates">
                                @foreach((array) session('misa_certificates.items') as $cert)
                                    @php($sn = data_get($cert, 'CertificateSN') ?? data_get($cert, 'certificateSN'))
                                    @if(!empty($sn))
                                        <option value="{{ $sn }}">
                                            {{ data_get($cert, 'AuthOrganizeName', '') }} {{ data_get($cert, 'ExpirationTime', '') ? ('- HSD: '.data_get($cert, 'ExpirationTime')) : '' }}
                                        </option>
                                    @endif
                                @endforeach
                            </datalist>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">InvSeries mặc định</label>
                        <input type="text" name="inv_series" class="form-control" value="{{ old('inv_series', $settings['inv_series'] ?? '') }}" placeholder="VD: 1C26TSS">
                        <div class="form-text">Dùng fallback khi API templates không trả đúng trường InvSeries.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">InvoiceTemplateID (webapp)</label>
                        <input type="text" name="invoice_template_id" class="form-control" value="{{ old('invoice_template_id', $settings['invoice_template_id'] ?? '') }}" placeholder="ID mẫu hóa đơn webapp">
                        <div class="form-text">Bắt buộc cho luồng <code>/webapp/insert</code> để tránh lỗi Invalid_InvoiceTemplateID.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Timeout (giây)</label>
                        <input type="number" name="timeout" class="form-control" min="5" max="120" value="{{ old('timeout', $settings['timeout'] ?? 30) }}">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lưu cấu hình</button>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Quay lại</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h2 class="h6 fw-bold mb-1">Kiểm tra kết nối MISA</h2>
                    <div class="text-muted small">Gọi thử API token để xem cấu hình đã đúng chưa.</div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <form method="POST" action="{{ route('admin.misa-meinvoice.settings.certificates') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">Lấy chứng thư số</button>
                    </form>
                    <form method="POST" action="{{ route('admin.misa-meinvoice.settings.webapp-templates') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning">Lấy mẫu webapp</button>
                    </form>
                    <form method="POST" action="{{ route('admin.misa-meinvoice.settings.test') }}">
                        @csrf
                        <button type="submit" class="btn btn-success">Test kết nối</button>
                    </form>
                </div>
            </div>

            @if(session('misa_test_connection'))
                @php($test = session('misa_test_connection'))
                <div class="alert alert-info">
                    <div><strong>Status:</strong> {{ $test['status'] ?? '---' }}</div>
                    <div><strong>Successful:</strong> {{ !empty($test['successful']) ? 'true' : 'false' }}</div>
                </div>
                <div class="bg-light p-3 rounded small" style="white-space: pre-wrap; word-break: break-word;">{{ $test['body'] ?? '' }}</div>
            @endif

            @if(session('misa_webapp_templates'))
                @php($tpl = session('misa_webapp_templates'))
                <div class="alert alert-warning mt-3 mb-2">
                    <div><strong>webapp/templates status:</strong> {{ $tpl['status'] ?? '---' }}</div>
                    <div><strong>Successful:</strong> {{ !empty($tpl['successful']) ? 'true' : 'false' }}</div>
                    <div><strong>Số lượng mẫu:</strong> {{ count((array) ($tpl['items'] ?? [])) }}</div>
                    <div class="small text-muted mt-1">Mẹo: chọn mẫu có đúng InvSeries, copy InvoiceTemplateID và dán vào ô trên.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">webapp/templates body (raw)</label>
                    <div class="bg-light p-3 rounded small" style="white-space: pre-wrap; word-break: break-word;">{{ $tpl['body'] ?? '' }}</div>
                </div>

                @if(!empty($tpl['items']) && is_array($tpl['items']))
                    <div class="table-responsive mb-3">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>InvoiceTemplateID</th>
                                    <th>InvSeries</th>
                                    <th>Tên mẫu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tpl['items'] as $item)
                                    @php($templateId = data_get($item, 'InvoiceTemplateID', data_get($item, 'invoiceTemplateID', data_get($item, 'IPTemplateID', data_get($item, 'ipTemplateID', '')))))
                                    @php($series = data_get($item, 'InvSeries', data_get($item, 'invSeries', data_get($item, 'OrgInvSeries', data_get($item, 'orgInvSeries', '')))))
                                    <tr>
                                        <td><code>{{ $templateId }}</code></td>
                                        <td>{{ $series }}</td>
                                        <td>{{ data_get($item, 'InvTemplateName', data_get($item, 'invTemplateName', data_get($item, 'TemplateName', data_get($item, 'templateName', '')))) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

            @if(session('misa_certificates'))
                @php($certs = session('misa_certificates'))
                <div class="alert alert-secondary mt-3 mb-2">
                    <div><strong>Token status:</strong> {{ $certs['token_status'] ?? '---' }}</div>
                    <div><strong>Token successful:</strong> {{ !empty($certs['token_successful']) ? 'true' : 'false' }}</div>
                    <div><strong>Token success field:</strong> {{ var_export($certs['token_success'] ?? null, true) }}</div>
                    <div><strong>Token errorCode:</strong> {{ $certs['token_error_code'] ?? '---' }}</div>
                    <div><strong>Get certificates status:</strong> {{ $certs['status'] ?? '---' }}</div>
                    <div><strong>Get certificates successful:</strong> {{ !empty($certs['successful']) ? 'true' : 'false' }}</div>
                    <div><strong>Get certificates success field:</strong> {{ var_export($certs['success'] ?? null, true) }}</div>
                    <div><strong>Get certificates errorCode:</strong> {{ $certs['error_code'] ?? '---' }}</div>
                    <div><strong>Get certificates descriptionErrorCode:</strong> {{ $certs['description_error_code'] ?? '---' }}</div>
                    <div><strong>Số lượng chứng thư:</strong> {{ count((array) ($certs['items'] ?? [])) }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Token API body (raw)</label>
                    <div class="bg-light p-3 rounded small" style="white-space: pre-wrap; word-break: break-word;">{{ $certs['token_body'] ?? '' }}</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Get certificates API body (raw)</label>
                    <div class="bg-light p-3 rounded small" style="white-space: pre-wrap; word-break: break-word;">{{ $certs['body'] ?? '' }}</div>
                </div>

                @if(!empty($certs['items']) && is_array($certs['items']))
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>CertificateSN</th>
                                    <th>TaxCode</th>
                                    <th>UserName</th>
                                    <th>Tổ chức</th>
                                    <th>Hiệu lực</th>
                                    <th>Hết hạn</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($certs['items'] as $cert)
                                    <tr>
                                        <td><code>{{ data_get($cert, 'CertificateSN', data_get($cert, 'certificateSN', '')) }}</code></td>
                                        <td>{{ data_get($cert, 'TaxCode', data_get($cert, 'taxCode', '')) }}</td>
                                        <td>{{ data_get($cert, 'UserName', data_get($cert, 'userName', '')) }}</td>
                                        <td>{{ data_get($cert, 'AuthOrganizeName', data_get($cert, 'authOrganizeName', '')) }}</td>
                                        <td>{{ data_get($cert, 'EffectiveTime', data_get($cert, 'effectiveTime', '')) }}</td>
                                        <td>{{ data_get($cert, 'ExpirationTime', data_get($cert, 'expirationTime', '')) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
