@extends('layouts.user')

@section('title', 'Đăng ký')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm" style="border-radius: 18px; overflow: hidden;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h4 class="fw-bold mb-0" style="color: var(--brand-secondary);">Đăng ký</h4>
                    <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 999px;">Về trang chủ</a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger" style="border-radius: 14px;">
                        <div class="fw-bold mb-1">Vui lòng kiểm tra lại</div>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success" style="border-radius: 14px;">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Họ và tên</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required autofocus autocomplete="name" style="border-radius: 12px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autocomplete="username" style="border-radius: 12px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required autocomplete="new-password" style="border-radius: 12px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nhập lại mật khẩu</label>
                        <input type="password" name="password_confirmation" class="form-control" required autocomplete="new-password" style="border-radius: 12px;">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius: 999px; padding: 12px 14px;">
                        Tạo tài khoản
                    </button>

                    <div class="text-center mt-3">
                        <span class="text-muted">Đã có tài khoản?</span>
                        <a href="{{ route('login') }}" class="fw-bold" style="color: var(--brand-secondary);">Đăng nhập</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
