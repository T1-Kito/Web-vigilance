@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4" style="display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap;">
        <h2 class="mb-0">{{ $customer->name }}</h2>
        <div style="display:flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i>Sửa</a>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-12">
                    <div class="fw-bold fs-6">Nhóm 1: Định danh</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold">MST/CCCD chủ hộ</div>
                    <div class="text-muted">{{ $customer->tax_id ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold">Tên công ty</div>
                    <div class="text-muted">{{ $customer->name ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold">Loại khách hàng</div>
                    <div class="text-muted">{{ $customer->customer_type ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold">Tình trạng</div>
                    <div class="text-muted">{{ $customer->company_status ?: '-' }}</div>
                </div>

                <div class="col-12">
                    <div class="fw-bold fs-6">Nhóm 2: Pháp lý</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold">Loại hình DN</div>
                    <div class="text-muted">{{ $customer->business_type ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold">Người đại diện</div>
                    <div class="text-muted">{{ $customer->representative ?: '-' }}</div>
                </div>

                <div class="col-12">
                    <div class="fw-bold fs-6">Nhóm 3: Địa chỉ</div>
                </div>
                <div class="col-12">
                    <div class="fw-bold">Địa chỉ thuế</div>
                    <div class="text-muted" style="white-space: pre-wrap;">{{ $customer->tax_address ?: '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="fw-bold">Địa chỉ liên hệ</div>
                    <div class="text-muted" style="white-space: pre-wrap;">{{ $customer->address ?: '-' }}</div>
                </div>

                <div class="col-12">
                    <div class="fw-bold fs-6">Nhóm 4: Liên hệ</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold">Số điện thoại</div>
                    <div class="text-muted">{{ $customer->phone ?: '-' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold">Email</div>
                    <div class="text-muted">{{ $customer->email ?: '-' }}</div>
                </div>

                <div class="col-12">
                    <div class="fw-bold fs-6">Nhóm 5: Hóa đơn</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold">Người nhận HĐ</div>
                    <div class="text-muted">{{ $customer->invoice_recipient ?: '-' }}</div>
                </div>

                <div class="col-12">
                    <div class="fw-bold fs-6">Nhóm 6: Khác</div>
                </div>
                <div class="col-md-6">
                    <div class="fw-bold">Cơ quan thuế quản lý</div>
                    <div class="text-muted">{{ $customer->managed_by ?: '-' }}</div>
                </div>
                <div class="col-12">
                    <div class="fw-bold">Ngành nghề chính</div>
                    <div class="text-muted" style="white-space: pre-wrap;">{{ $customer->main_business ?: '-' }}</div>
                </div>
            </div>

            <hr class="my-4">
            <div class="row g-3 small text-muted">
                <div class="col-md-6">Ngày tạo: {{ optional($customer->created_at)->format('d/m/Y H:i') ?: '-' }}</div>
                <div class="col-md-6">Cập nhật lần cuối: {{ optional($customer->updated_at)->format('d/m/Y H:i') ?: '-' }}</div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Xóa khách hàng này?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Xóa khách hàng</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
