@extends('layouts.admin')

@section('title', 'Quản lý bảo hành')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0" style="color: #2c3e50; font-weight: 700;">
                <i class="bi bi-shield-check me-2" style="color: #3498db;"></i> Quản lý bảo hành
            </h1>
            <p class="text-muted mb-0" style="font-size: 1.1em;">Quản lý thông tin bảo hành sản phẩm</p>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('admin.warranties.create') }}" class="btn btn-primary" style="border-radius: 12px; padding: 12px 24px; font-weight: 600; box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);">
                <i class="bi bi-plus-circle me-2"></i> Thêm bảo hành
            </a>
            <a href="{{ route('admin.warranties.exportExcel') }}" class="btn btn-success" style="border-radius: 12px; padding: 12px 24px; font-weight: 600; box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);">
                <i class="bi bi-download me-2"></i> Xuất Excel
            </a>
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importModal" style="border-radius: 12px; padding: 12px 24px; font-weight: 600; box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);">
                <i class="bi bi-upload me-2"></i> Import Excel
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAllModal" style="border-radius: 12px; padding: 12px 24px; font-weight: 600; box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);">
                <i class="bi bi-trash me-2"></i> Xóa tất cả
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border: none; border-radius: 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-1" style="font-size: 0.9em; font-weight: 600;">TỔNG BẢO HÀNH</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $stats['total'] ?? $warranties->total() }}</div>
                        </div>
                        <div class="text-white-50">
                            <i class="bi bi-shield-check" style="font-size: 2.5em;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border: none; border-radius: 16px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; box-shadow: 0 8px 25px rgba(240, 147, 251, 0.3);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-1" style="font-size: 0.9em; font-weight: 600;">CÒN HIỆU LỰC</div>
                            <div class="h3 mb-0 font-weight-bold">
                                {{ $stats['active'] ?? 0 }}
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="bi bi-check-circle" style="font-size: 2.5em;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border: none; border-radius: 16px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-1" style="font-size: 0.9em; font-weight: 600;">HẾT HẠN</div>
                            <div class="h3 mb-0 font-weight-bold">
                                {{ $stats['expired'] ?? 0 }}
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="bi bi-exclamation-triangle" style="font-size: 2.5em;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card h-100" style="border: none; border-radius: 16px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; box-shadow: 0 8px 25px rgba(67, 233, 123, 0.3);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 mb-1" style="font-size: 0.9em; font-weight: 600;">YÊU CẦU MỚI</div>
                            <div class="h3 mb-0 font-weight-bold">
                                {{ \App\Models\WarrantyClaim::where('claim_status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="text-white-50">
                            <i class="bi bi-clock" style="font-size: 2.5em;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card shadow mb-4" style="border: none; border-radius: 16px; overflow: hidden;">
        <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
            <h6 class="m-0 font-weight-bold" style="font-size: 1.2em;">Danh sách bảo hành</h6>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.warranties.claims') }}" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3); border-radius: 8px;">
                    <i class="bi bi-list-check me-1"></i> Yêu cầu bảo hành
                </a>
            </div>
        </div>
        
        <!-- Search Form -->
        <div class="card-body" style="background: #f8f9fa; border-bottom: 1px solid #e9ecef;">
            <form action="{{ route('admin.warranties.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="serial_search" class="form-label fw-bold" style="color: #495057;">Tìm kiếm theo số seri:</label>
                    <input type="text" 
                           class="form-control" 
                           id="serial_search" 
                           name="serial_search" 
                           value="{{ request('serial_search') }}"
                           placeholder="Nhập số seri cần tìm..."
                           style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; font-size: 1em;">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="model_search" class="form-label fw-bold" style="color: #495057;">Tìm kiếm theo model:</label>
                    <input type="text"
                           class="form-control"
                           id="model_search"
                           name="model_search"
                           value="{{ request('model_search') }}"
                           placeholder="Nhập model / tên sản phẩm..."
                           style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; font-size: 1em;">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="customer_search" class="form-label fw-bold" style="color: #495057;">Tìm theo tên khách hàng:</label>
                    <input type="text"
                           class="form-control"
                           id="customer_search"
                           name="customer_search"
                           value="{{ request('customer_search') }}"
                           placeholder="Nhập tên khách hàng..."
                           style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; font-size: 1em;">
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <label for="purchase_date_search" class="form-label fw-bold" style="color: #495057;">Ngày mua:</label>
                    <input type="date"
                           class="form-control"
                           id="purchase_date_search"
                           name="purchase_date_search"
                           value="{{ request('purchase_date_search') }}"
                           style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px; font-size: 1em;">
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <button class="btn w-100" type="submit" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 12px; padding: 12px 20px; font-weight: 600;">
                        <i class="bi bi-search me-1"></i> Tìm kiếm
                    </button>
                </div>
                <div class="col-12 col-md-3 col-lg-2">
                    <a href="{{ route('admin.warranties.index') }}" class="btn btn-outline-secondary w-100" style="border-radius: 12px; padding: 12px; font-weight: 600;">
                        <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
                    </a>
                </div>
            </form>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(request('serial_search'))
                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert" style="border-radius: 12px; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="bi bi-search me-2"></i>
                    <strong>Kết quả tìm kiếm:</strong> Tìm thấy {{ $warranties->total() }} bảo hành cho số seri "{{ request('serial_search') }}"
                    <a href="{{ route('admin.warranties.index') }}" class="btn btn-sm btn-light ms-2" style="border-radius: 8px;">
                        <i class="bi bi-x-circle me-1"></i> Xóa tìm kiếm
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(request('model_search') || request('customer_search') || request('purchase_date_search'))
                <div class="alert alert-info alert-dismissible fade show mb-3" role="alert" style="border-radius: 12px; border: none; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="bi bi-search me-2"></i>
                    <strong>Kết quả tìm kiếm:</strong>
                    @if(request('model_search'))
                        Model "{{ request('model_search') }}"
                    @endif
                    @if(request('customer_search'))
                        @if(request('model_search'))
                            ,
                        @endif
                        Khách hàng "{{ request('customer_search') }}"
                    @endif
                    @if(request('purchase_date_search'))
                        @if(request('model_search') || request('customer_search'))
                            ,
                        @endif
                        Ngày mua {{ \Carbon\Carbon::parse(request('purchase_date_search'))->format('d/m/Y') }}
                    @endif
                    : tìm thấy {{ $warranties->total() }} bảo hành
                    @if(request('customer_search') && request('purchase_date_search'))
                        @if(!is_null($customerDayOrderCount))
                            . Tổng số đơn (theo mã HĐ) trong ngày: <strong>{{ $customerDayOrderCount }}</strong>
                        @endif
                    @endif
                    <a href="{{ route('admin.warranties.index') }}" class="btn btn-sm btn-light ms-2" style="border-radius: 8px;">
                        <i class="bi bi-x-circle me-1"></i> Xóa tìm kiếm
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table" id="dataTable" width="100%" cellspacing="0" style="border: none;">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">ID</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Số seri</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Sản phẩm / Model</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Khách hàng</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Ngày mua</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Hết hạn</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Trạng thái</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Yêu cầu</th>
                            <th style="border: none; padding: 16px 12px; font-weight: 700; color: #495057;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warranties as $warranty)
                        <tr style="border-bottom: 1px solid #f1f3f4; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='white'">
                            <td style="border: none; padding: 16px 12px; font-weight: 600; color: #6c757d;">{{ $warranty->id }}</td>
                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex flex-column">
                                    <strong style="color: #2c3e50; font-size: 1.1em;">{{ $warranty->serial_number }}</strong>
                                    @if($warranty->invoice_number)
                                        <small class="text-muted" style="font-size: 0.9em;">Mã HĐ: {{ $warranty->invoice_number }}</small>
                                    @endif
                                </div>
                            </td>
                            <td style="border: none; padding: 16px 12px;">
                                @if($warranty->model_name || $warranty->product)
                                    <div class="d-flex flex-column">
                                        <strong style="color: #2c3e50; font-size: 1.05em;">
                                            {{ $warranty->model_name ?? optional($warranty->product)->name }}
                                        </strong>
                                        @if($warranty->product && $warranty->model_name && $warranty->product->name !== $warranty->model_name)
                                            <small class="text-muted" style="font-size: 0.85em;">
                                                Sản phẩm gốc: {{ $warranty->product->name }}
                                            </small>
                                        @endif
                                        @if(optional($warranty->product)->category)
                                            <small class="text-muted" style="font-size: 0.85em;">
                                                Danh mục: {{ $warranty->product->category->name }}
                                            </small>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="border: none; padding: 16px 12px;">
                                @if($warranty->customer_name || $warranty->customer_phone || $warranty->customer_email)
                                <div class="d-flex flex-column">
                                    @if($warranty->customer_name)
                                        <strong style="color: #2c3e50; font-size: 1.1em;">{{ $warranty->customer_name }}</strong>
                                    @endif
                                    @if($warranty->customer_phone)
                                        <small class="text-muted" style="font-size: 0.9em;">{{ $warranty->customer_phone }}</small>
                                    @endif
                                    @if($warranty->customer_email)
                                        <small class="text-muted" style="font-size: 0.9em;">{{ $warranty->customer_email }}</small>
                                    @endif
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td style="border: none; padding: 16px 12px; font-weight: 600; color: #495057;">{{ $warranty->purchase_date->format('d/m/Y') }}</td>
                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex flex-column">
                                    <span style="font-weight: 600; color: #495057;">{{ $warranty->warranty_end_date->format('d/m/Y') }}</span>
                                    @if($warranty->is_expired)
                                        <small class="text-danger" style="font-weight: 600;">{{ $warranty->expired_time_text }}</small>
                                    @else
                                        <small class="text-success" style="font-weight: 600;">{{ $warranty->remaining_time_text }}</small>
                                    @endif
                                </div>
                            </td>
                            <td style="border: none; padding: 16px 12px;">
                                <span class="badge" style="background: {{ $warranty->warranty_status_color == 'success' ? 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)' : ($warranty->warranty_status_color == 'danger' ? 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)') }}; color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">
                                    {{ $warranty->warranty_status_text }}
                                </span>
                            </td>
                            <td style="border: none; padding: 16px 12px;">
                                @php $pendingClaims = $warranty->claims->where('claim_status', 'pending')->count(); @endphp
                                @if($pendingClaims > 0)
                                    <span class="badge" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">{{ $pendingClaims }} chờ xử lý</span>
                                @else
                                    <span class="badge" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 0.9em;">{{ $warranty->claims->count() }} yêu cầu</span>
                                @endif
                            </td>
                            <td style="border: none; padding: 16px 12px;">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.warranties.show', $warranty) }}" 
                                       class="btn btn-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; padding: 8px 12px; font-weight: 600; box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.warranties.edit', $warranty) }}" 
                                       class="btn btn-sm" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: white; border: none; border-radius: 8px; padding: 8px 12px; font-weight: 600; box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.warranties.destroy', $warranty) }}" 
                                          method="POST" class="d-inline" 
                                          onsubmit="return confirm('Bạn có chắc muốn xóa bảo hành này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; border: none; border-radius: 8px; padding: 8px 12px; font-weight: 600; box-shadow: 0 4px 12px rgba(250, 112, 154, 0.3);" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $warranties->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<!-- Delete All Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold" id="deleteAllModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Xác nhận xóa tất cả bảo hành
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-danger mb-3">
                    <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-triangle-fill me-2"></i>Cảnh báo nguy hiểm!</h6>
                    <p class="mb-0">Bạn sắp xóa <strong>TẤT CẢ</strong> bảo hành trong hệ thống. Hành động này <strong>KHÔNG THỂ HOÀN TÁC</strong>!</p>
                </div>
                <p class="mb-3">Tổng số bảo hành sẽ bị xóa: <strong class="text-danger">{{ $stats['total'] ?? $warranties->total() }}</strong></p>
                <p class="mb-3">Vui lòng nhập <strong>"XÓA TẤT CẢ"</strong> vào ô bên dưới để xác nhận:</p>
                <form action="{{ route('admin.warranties.destroyAll') }}" method="POST" id="deleteAllForm">
                    @csrf
                    @method('DELETE')
                    <div class="mb-3">
                        <input type="text" 
                               class="form-control" 
                               id="confirmDeleteInput" 
                               name="confirm_text" 
                               placeholder="Nhập: XÓA TẤT CẢ"
                               required
                               autocomplete="off"
                               style="border-radius: 12px; padding: 12px; font-size: 1em; border: 2px solid #dc3545;">
                        <small class="text-muted">Vui lòng nhập chính xác: <strong>XÓA TẤT CẢ</strong></small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary flex-fill" data-bs-dismiss="modal" style="border-radius: 12px; padding: 12px; font-weight: 600;">
                            <i class="bi bi-x-circle me-2"></i> Hủy
                        </button>
                        <button type="submit" 
                                class="btn btn-danger flex-fill" 
                                id="confirmDeleteBtn"
                                disabled
                                style="border-radius: 12px; padding: 12px; font-weight: 600;">
                            <i class="bi bi-trash me-2"></i> Xóa tất cả
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title fw-bold" id="importModalLabel">
                    <i class="bi bi-upload me-2"></i> Import Excel Bảo Hành
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info mb-3">
                    <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Hướng dẫn format file Excel:</h6>
                    <ul class="mb-0 small">
                        <li><strong>SERI</strong> (bắt buộc): Số seri sản phẩm</li>
                        <li><strong>CÔNG TY</strong> (tùy chọn): Tên khách hàng/công ty</li>
                        <li><strong>NGÀY</strong> (tùy chọn): Ngày mua và ngày bắt đầu bảo hành (format: dd/mm/yyyy, ví dụ: 31/08/2023)</li>
                        <li><strong>Số lượng</strong>: Cột này sẽ được bỏ qua, không xử lý</li>
                    </ul>
                    <p class="mb-0 mt-2 small"><strong>Lưu ý:</strong> Thời hạn bảo hành sẽ tự động là 12 tháng. Nếu không có ngày, hệ thống sẽ dùng ngày hiện tại.</p>
                </div>
                <form action="{{ route('admin.warranties.importExcel') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn file (.xlsx, .xls)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-1"></i> Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

 

<script>
$(document).ready(function() {
    // Auto focus vào ô tìm kiếm khi có giá trị
    if ($('#serial_search').val()) {
        $('#serial_search').focus();
    }
    
    // Clear search khi nhấn Escape
    $(document).keyup(function(e) {
        if (e.key === "Escape") {
            window.location.href = "{{ route('admin.warranties.index') }}";
        }
    });
    
    // Không dùng DataTable vì đã có Laravel pagination
    // DataTable sẽ xung đột với server-side pagination
    
    // Xử lý xác nhận xóa tất cả - dùng cả jQuery và vanilla JS để đảm bảo hoạt động
    function checkDeleteConfirmation() {
        const input = document.getElementById('confirmDeleteInput');
        const button = document.getElementById('confirmDeleteBtn');
        
        if (!input || !button) return;
        
        const confirmText = input.value.trim();
        const requiredText = 'XÓA TẤT CẢ';
        
        if (confirmText === requiredText) {
            button.disabled = false;
            button.classList.remove('disabled');
        } else {
            button.disabled = true;
            button.classList.add('disabled');
        }
    }
    
    // Dùng event delegation với jQuery
    $(document).on('input keyup paste change', '#confirmDeleteInput', function() {
        checkDeleteConfirmation();
    });
    
    // Dùng vanilla JS như backup
    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'confirmDeleteInput') {
            checkDeleteConfirmation();
        }
    });
    
    // Khi modal được hiển thị, đảm bảo input được focus và reset
    $('#deleteAllModal').on('shown.bs.modal', function() {
        const input = document.getElementById('confirmDeleteInput');
        const button = document.getElementById('confirmDeleteBtn');
        if (input) {
            input.value = '';
            input.focus();
        }
        if (button) {
            button.disabled = true;
            button.classList.add('disabled');
        }
    });
    
    // Reset khi modal đóng
    $('#deleteAllModal').on('hidden.bs.modal', function() {
        const input = document.getElementById('confirmDeleteInput');
        const button = document.getElementById('confirmDeleteBtn');
        if (input) input.value = '';
        if (button) {
            button.disabled = true;
            button.classList.add('disabled');
        }
    });
    
    // Xác nhận lại trước khi submit
    $(document).on('submit', '#deleteAllForm', function(e) {
        const confirmText = document.getElementById('confirmDeleteInput').value.trim();
        const requiredText = 'XÓA TẤT CẢ';
        
        if (confirmText !== requiredText) {
            e.preventDefault();
            alert('Vui lòng nhập chính xác "XÓA TẤT CẢ" để xác nhận!');
            return false;
        }
        
        return confirm('Bạn có CHẮC CHẮN muốn xóa TẤT CẢ bảo hành? Hành động này KHÔNG THỂ HOÀN TÁC!');
    });
});
</script>
@endsection 