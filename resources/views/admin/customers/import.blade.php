@extends('layouts.admin')

@section('title', 'Import khách hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4" style="display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap;">
        <h2 class="mb-0">Import khách hàng</h2>
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

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('admin.customers.importExcel') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold">File Excel (.xlsx/.xls)</label>
                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                    <div class="form-text">
                        Header cần có đúng 6 cột: Tên khách hàng / MST/CCCD chủ hộ / Địa chỉ / Người nhận HĐ / Email / Số điện thoại
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i>Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
