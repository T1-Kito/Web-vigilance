@extends('layouts.user')

@section('title', 'Tra cứu bảo hành - Vigilance')

@section('content')
<div class="warranty-result-bg">
    <div class="container py-4 warranty-result-container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 warranty-page-card">
                    <div class="card-body warranty-page-body">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <h1 class="fw-bold mb-3" style="color:#007BFF; font-size:2em;">
                            <i class="bi bi-shield-check"></i> Kết quả tra cứu bảo hành
                        </h1>
                        <p class="text-muted" style="font-size:1.05em;">
                            Số seri: <strong class="text-dark">{{ $serialNumber }}</strong>
                        </p>
                    </div>

                    <!-- Search Again Button -->
                    <div class="text-center mb-4">
                        <a href="{{ route('warranty.check') }}" class="btn btn-outline-primary px-4 py-2 fw-semibold">
                            <i class="bi bi-search me-2"></i>Tra cứu khác
                        </a>
                    </div>

                    @php
                        $errorText = session('warranty_error') ?? ($errorMessage ?? null);
                    @endphp

                    @if($errorText)
                        <!-- Error Message -->
                        <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 15px;">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-exclamation-triangle-fill me-3" style="font-size:1.5em;"></i>
                                <div>
                                    <h5 class="fw-bold mb-1">Không tìm thấy thông tin bảo hành</h5>
                                    <p class="mb-0">{{ $errorText }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Help Section -->
                        <div class="card border-0 shadow-sm mt-4" style="border-radius: 15px;">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3 text-primary">
                                    <i class="bi bi-question-circle"></i> Cần hỗ trợ?
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="bi bi-telephone text-success me-3" style="font-size:1.5em;"></i>
                                            <div>
                                                <strong>Gọi điện:</strong><br>
 <a href="tel:0968220919" class="text-muted" style="text-decoration: none;">0968220919</a>                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(isset($warranty))
                        @php
                            $customerNameMasked = null;
                            if (!empty($warranty->customer_name)) {
                                $parts = preg_split('/\s+/', trim($warranty->customer_name));
                                $last = $parts ? end($parts) : '';
                                $customerNameMasked = '******' . ($last ? (' ' . $last) : '');
                            }
                            $isActiveWarranty = !$warranty->is_expired && $warranty->status !== 'cancelled';
                        @endphp
                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm" style="border-radius: 15px;">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Warranty Information -->
                        <div class="warranty-mock-card">
                            <div class="warranty-mock-body">
                                <div class="warranty-panel">
                                    <div class="warranty-mock-status">
                                        <span class="warranty-mock-pill {{ $isActiveWarranty ? 'is-active' : 'is-expired' }}">
                                            <i class="bi {{ $isActiveWarranty ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                                            {{ $isActiveWarranty ? 'Còn bảo hành' : 'Hết bảo hành' }}
                                        </span>
                                    </div>

                                    <div class="warranty-mock-divider"></div>

                                    <div class="warranty-mock-row split">
                                        <div class="warranty-mock-left">
                                            <span class="warranty-mock-ico is-model"><i class="bi bi-box"></i></span>
                                            <span class="warranty-mock-label">Model:</span>
                                        </div>
                                        <div class="warranty-mock-value">{{ $warranty->model_name ?? optional($warranty->product)->name ?? '-' }}</div>
                                    </div>
                                    <div class="warranty-mock-divider"></div>

                                    <div class="warranty-mock-row split">
                                        <div class="warranty-mock-left">
                                            <span class="warranty-mock-ico is-serial"><i class="bi bi-upc-scan"></i></span>
                                            <span class="warranty-mock-label">Số serial:</span>
                                        </div>
                                        <div class="warranty-mock-value">{{ $warranty->serial_number }}</div>
                                    </div>
                                    <div class="warranty-mock-divider"></div>

                                    <div class="warranty-mock-row split">
                                        <div class="warranty-mock-left">
                                            <span class="warranty-mock-ico is-status"><i class="bi bi-shield-check"></i></span>
                                            <span class="warranty-mock-label">Trạng thái bảo hành:</span>
                                        </div>
                                        <div class="warranty-mock-value" style="color: {{ $isActiveWarranty ? '#166534' : '#991b1b' }};">
                                            {{ $isActiveWarranty ? 'Còn bảo hành' : 'Hết bảo hành' }}
                                        </div>
                                    </div>
                                    <div class="warranty-mock-divider"></div>

                                    <div class="warranty-mock-row split">
                                        <div class="warranty-mock-left">
                                            <span class="warranty-mock-ico is-customer"><i class="bi bi-person"></i></span>
                                            <span class="warranty-mock-label">Khách hàng:</span>
                                        </div>
                                        <div class="warranty-mock-value">{{ $customerNameMasked ?? '******' }}</div>
                                    </div>

                                    <div class="warranty-mock-support">
                                        <div class="warranty-mock-support-inner">
                                            <i class="bi bi-headset" style="opacity:.85;"></i>
                                            <span>
                                                <span>Bạn có thắc mắc về thông tin bảo hành?</span>
                                                <br>
                                                <span>
                                                    Vui lòng liên hệ hotline
                                                    <a href="tel:0968220919" class="warranty-mock-phone">0968 220 919</a>
                                                    để được hỗ trợ nhanh nhất.
                                                </span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.9em !important;
}

.warranty-result-bg {
    background: #eaf7f5;
    min-height: calc(100vh - 80px);
}

.warranty-result-container {
    padding-top: 34px;
    padding-bottom: 38px;
}

.warranty-page-card {
    border-radius: 24px;
    background: transparent;
    border: 0;
    box-shadow: none;
    max-width: 760px;
    margin-left: auto;
    margin-right: auto;
}

.warranty-page-body {
    padding: 18px 18px 18px 18px;
}

.warranty-mock-card {
    margin-top: 8px;
}

.warranty-mock-body {
    padding: 0;
}

.warranty-panel {
    max-width: 560px;
    margin-left: auto;
    margin-right: auto;
    border-radius: 18px;
    background: #fff;
    border: 1px solid rgba(15, 23, 42, 0.10);
    box-shadow: 0 18px 45px rgb(33 150 243 / 17%) !important;
    padding: 18px 20px;
}

.warranty-mock-status {
    display: flex;
    justify-content: center;
}

.warranty-mock-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 18px;
    border-radius: 999px;
    font-size: 1rem;
    border: 1px solid rgba(15, 23, 42, 0.08);
}

.warranty-mock-pill.is-active {
    background: rgba(22, 163, 74, 0.12);
    color: #166534;
}

.warranty-mock-pill.is-expired {
    background: rgba(220, 38, 38, 0.12);
    color: #991b1b;
}

.warranty-mock-divider {
    height: 1px;
    background: rgba(15, 23, 42, 0.08);
    margin: 12px 0;
}

.warranty-mock-row {
    padding: 2px 2px;
}

.warranty-mock-row.split {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.warranty-mock-left {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    min-width: 160px;
    font-size: 1.3em;
}

.warranty-mock-ico {
    width: 22px;
    height: 22px;
    border-radius: 7px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    border: 1px solid rgba(15, 23, 42, 0.10);
}

.warranty-mock-ico.is-model {
    background: rgba(249, 115, 22, 0.14);
    color: #c2410c;
}

.warranty-mock-ico.is-serial {
    background: rgba(59, 130, 246, 0.14);
    color: #1d4ed8;
}

.warranty-mock-ico.is-customer {
    background: rgba(34, 197, 94, 0.14);
    color: #15803d;
}

.warranty-mock-ico.is-status {
    background: rgba(20, 184, 166, 0.14);
    color: #0f766e;
}

.warranty-mock-label {
    color: #64748b;
}

.warranty-mock-value {
    color: #0f172a;
    font-size: 1.3rem;
    word-break: break-word;
}

.warranty-mock-support {
    margin-top: 14px;
    border-radius: 14px;
    border: 1px solid rgba #e91e635c
    padding: 12px 12px;
}

.warranty-mock-support-inner {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #334155;
    border-radius: 14px;
    padding: 8px;
    border: 1px solid rgba(34, 60, 206, 0.14) ;
}

.warranty-mock-phone {
    font-weight: 900;
    color: #f72e2e;
    text-decoration: none;
    padding: 2px 8px;
    border-radius: 10px;
   
}

@media (max-width: 575.98px) {
    .warranty-page-body {
        padding: 18px 16px 16px 16px;
    }
    .warranty-result-container {
        padding-top: 22px;
        padding-bottom: 26px;
    }
    .warranty-panel {
        padding: 14px 14px;
    }
    .warranty-mock-row.split {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endsection