@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h3>Thêm banner</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="mt-3">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Vị trí</label>
                <select name="position" class="form-select" required>
                    <option value="top_strip">Thanh banner trên cùng (mỏng)</option>
                    <option value="side_left">Banner bên trái (dọc)</option>
                    <option value="side_right">Banner bên phải (dọc)</option>
                    <option value="home_promo">Banner dưới hero (nhỏ)</option>
                    <option value="general">Khác</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tiêu đề (tuỳ chọn)</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Link (tuỳ chọn)</label>
                <input type="text" name="link_url" class="form-control" value="{{ old('link_url') }}" placeholder="https://...">
            </div>
            <div class="col-md-8">
                <label class="form-label">File banner (ảnh hoặc video)</label>
                <input type="file" name="image" class="form-control" required accept="image/*,video/mp4,video/webm">
                <div class="form-text">
                    Vị trí <strong>Thanh banner trên cùng</strong> hỗ trợ <code>mp4/webm</code>. Các vị trí khác chỉ nhận ảnh.
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                    <label class="form-check-label" for="is_active">Hiển thị</label>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">Quay lại</a>
            <button class="btn btn-primary" type="submit">Lưu</button>
        </div>
    </form>
</div>
@endsection


