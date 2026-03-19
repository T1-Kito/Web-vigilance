@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h3>Sửa banner</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.banners.update', $banner) }}" method="POST" enctype="multipart/form-data" class="mt-3">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Vị trí</label>
                <select name="position" class="form-select" required>
                    <option value="top_strip" {{ ($banner->position ?? 'general') === 'top_strip' ? 'selected' : '' }}>Thanh banner trên cùng (mỏng)</option>
                    <option value="side_left" {{ ($banner->position ?? 'general') === 'side_left' ? 'selected' : '' }}>Banner bên trái (dọc)</option>
                    <option value="side_right" {{ ($banner->position ?? 'general') === 'side_right' ? 'selected' : '' }}>Banner bên phải (dọc)</option>
                    <option value="home_promo" {{ ($banner->position ?? 'general') === 'home_promo' ? 'selected' : '' }}>Banner dưới hero (nhỏ)</option>
                    <option value="general" {{ ($banner->position ?? 'general') === 'general' ? 'selected' : '' }}>Khác</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Tiêu đề (tuỳ chọn)</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $banner->title) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Link (tuỳ chọn)</label>
                <input type="text" name="link_url" class="form-control" value="{{ old('link_url', $banner->link_url) }}" placeholder="https://...">
            </div>
            <div class="col-md-8">
                <label class="form-label">File banner (ảnh hoặc video)</label>
                <input type="file" name="image" class="form-control" accept="image/*,video/mp4,video/webm">
                <div class="form-text">
                    Vị trí <strong>Thanh banner trên cùng</strong> hỗ trợ <code>mp4/webm</code>. Các vị trí khác chỉ nhận ảnh.
                </div>
                @if($banner->image_path)
                    <div class="mt-2">
                        @if($banner->is_video)
                            <video src="{{ $banner->media_url }}" muted loop playsinline style="max-width:360px; width:100%; border-radius:8px;" controls></video>
                        @else
                            <img src="{{ $banner->image_url ?: asset($banner->image_path) }}" alt="{{ $banner->title }}" style="max-width:300px;border-radius:8px;">
                        @endif
                    </div>
                @endif
            </div>
            <div class="col-md-4">
                <label class="form-label">Thứ tự</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $banner->sort_order) }}" min="0">
                <div class="form-check mt-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" {{ $banner->is_active ? 'checked' : '' }}>
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


