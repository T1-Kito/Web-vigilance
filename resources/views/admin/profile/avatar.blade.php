@extends('layouts.admin')

@section('title', 'Đổi avatar')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-person-circle"></i> Đổi avatar
            </h1>
            <p class="text-muted">Tải ảnh đại diện mới (JPG/PNG/WEBP, tối đa 2MB)</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="text-center">
                        @if(auth()->user() && auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="Avatar" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #e5e7eb;">
                        @else
                            <div style="width: 120px; height: 120px; border-radius: 50%; display:flex; align-items:center; justify-content:center; background: linear-gradient(45deg, #3b82f6, #8b5cf6); color:white; font-weight:700; font-size:32px; margin:0 auto;">
                                {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-8">
                    <form method="POST" action="{{ route('admin.profile.avatar.update') }}" enctype="multipart/form-data" class="row g-2">
                        @csrf

                        <div class="col-12">
                            <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*" required>
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Cập nhật avatar
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                                Quản lý người dùng
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
