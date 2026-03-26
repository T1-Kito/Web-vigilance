@extends('layouts.admin')

@section('title', 'Thêm khách hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4" style="display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap;">
        <h2 class="mb-0">Thêm khách hàng</h2>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.customers.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Tên khách hàng</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" maxlength="255" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">MST/CCCD chủ hộ</label>
                        <input type="text" name="tax_id" class="form-control" value="{{ old('tax_id') }}" maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" maxlength="30">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Người nhận HĐ</label>
                        <input type="text" name="invoice_recipient" class="form-control" value="{{ old('invoice_recipient') }}" maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
