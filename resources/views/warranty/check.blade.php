@extends('layouts.user')

@section('title', 'Tra cứu bảo hành - Vigilance')

@section('content')
<div class="container py-4">
    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 15px;">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-3" style="font-size:1.5em;"></i>
                <div>
                    <h5 class="fw-bold mb-1">Thành công!</h5>
                    <p class="mb-0">{{ session('success') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Error Message -->
    @if(session('warranty_error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 15px;">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3" style="font-size:1.5em;"></i>
                <div>
                    <h5 class="fw-bold mb-1">Lỗi!</h5>
                    <p class="mb-0">{{ session('warranty_error') }}</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="text-center mb-5">
                <h1 class="fw-bold mb-3" style="color:#007BFF; font-size:2.5em;">
                    <i class="bi bi-shield-check"></i> Tra cứu bảo hành
                </h1>
                <p class="text-muted" style="font-size:1.1em;">
                    Nhập số seri (SN) sản phẩm để kiểm tra thông tin bảo hành
                </p>
            </div>

            <!-- Search Form -->
            <div class="card shadow-lg border-0" style="border-radius: 20px;">
                <div class="card-body p-5">
                    <form action="{{ route('warranty.search') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="serial_number" class="form-label fw-bold" style="font-size:1.2em; color:#333;">
                                <i class="bi bi-upc-scan"></i> Số seri (SN) sản phẩm
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-primary text-white border-0" style="border-radius: 12px 0 0 12px;">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-0" 
                                       id="serial_number" 
                                       name="serial_number" 
                                       placeholder="Ví dụ: SN123456789" 
                                       value="{{ old('serial_number') }}"
                                       style="border-radius: 0 12px 12px 0; font-size:1.1em; padding: 15px 20px;"
                                       required>
                            </div>
                            @error('serial_number')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold" 
                                    style="border-radius: 12px; padding: 15px; font-size:1.2em; background: linear-gradient(135deg, #007BFF 0%, #0056b3 100%); border: none;">
                                <i class="bi bi-search"></i> Tra cứu ngay
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Cards -->
            <div class="row mt-5 g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-clock-history text-primary" style="font-size:3em;"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Kiểm tra nhanh</h5>
                            <p class="text-muted mb-0">Tra cứu thông tin bảo hành chỉ trong vài giây</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-shield-check text-success" style="font-size:3em;"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Bảo hành chính hãng</h5>
                            <p class="text-muted mb-0">Đảm bảo quyền lợi bảo hành chính hãng</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px;">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="bi bi-headset text-info" style="font-size:3em;"></i>
                            </div>
                            <h5 class="fw-bold mb-2">Hỗ trợ 24/7</h5>
                            <p class="text-muted mb-0">Đội ngũ kỹ thuật hỗ trợ tận tâm</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-5">
                <div class="card border-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 15px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3 text-primary">
                            <i class="bi bi-info-circle"></i> Hướng dẫn tra cứu
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Tìm số seri trên sản phẩm
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Nhập chính xác vào ô tìm kiếm
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Nhấn "Tra cứu ngay"
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle text-success me-2"></i>
                                        Xem kết quả chi tiết
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,123,255,0.4);
}

.input-group:focus-within {
    transform: scale(1.02);
    transition: transform 0.3s ease;
}
</style>
@endsection 