@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Quản lý Banner</h3>
        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">Thêm banner</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Ảnh</th>
                    <th>Vị trí</th>
                    <th>Tiêu đề</th>
                    <th>Link</th>
                    <th>Thứ tự</th>
                    <th>Hiển thị</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($banners as $banner)
                    <tr>
                        <td>{{ $banner->id }}</td>
                        <td style="width:200px">
                            @if($banner->image_path)
                                <img src="{{ asset($banner->image_path) }}" alt="{{ $banner->title }}" style="width:100%;max-width:180px;border-radius:8px;">
                            @endif
                        </td>
                        <td>
                            {{ $banner->position ?? 'general' }}
                        </td>
                        <td>{{ $banner->title }}</td>
                        <td>
                            @if($banner->link_url)
                                <a href="{{ $banner->link_url }}" target="_blank">{{ $banner->link_url }}</a>
                            @endif
                        </td>
                        <td>{{ $banner->sort_order }}</td>
                        <td>
                            <span class="badge {{ $banner->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $banner->is_active ? 'Hiển thị' : 'Ẩn' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-sm btn-warning">Sửa</a>
                            <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Xóa banner này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger" type="submit">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Chưa có banner nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $banners->links() }}
</div>
@endsection


