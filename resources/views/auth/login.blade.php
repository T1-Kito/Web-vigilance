@extends('layouts.user')

@section('title', 'Đăng nhập')

@section('content')
<style>
    .auth-mobile { max-width: 520px; margin: 0 auto; }

    .auth-desktop-wrap {
        width: 100%;
        min-height: calc(100vh - 220px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 28px 12px;
    }

    .auth-desktop-card {
        width: min(1000px, calc(100vw - 64px));
        margin-inline: auto;
        border-radius: 24px;
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    }

    .auth-brand-panel {
        position: relative;
        min-height: 600px;
        background-image: url('{{ asset('Gemini_Generated_Image_17ykzn17ykzn17yk.png') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 46px 36px;
    }

    .auth-brand-inner {
        position: relative;
        z-index: 1;
        max-width: 380px;
        text-align: center;
    }

    .auth-brand-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        padding: 11px 26px;
        border: 1.5px solid rgba(255,255,255,.92);
        color: #fff;
        text-decoration: none;
        font-weight: 700;
        letter-spacing: 0.2px;
        transition: all .2s ease;
    }

    .auth-brand-btn:hover {
        color: #fff;
        background: rgba(255,255,255,.16);
    }

    .auth-form-panel {
        min-height: 600px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 46px 36px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    }

    .auth-form-box {
        width: 100%;
        max-width: 390px;
    }

    .auth-form-control {
        height: 48px;
        border-radius: 11px;
        border-color: #d7dfeb;
        padding-inline: 14px;
    }

    .auth-form-control:focus {
        border-color: #4f7ad9;
        box-shadow: 0 0 0 0.2rem rgba(79, 122, 217, 0.14);
    }

    .auth-submit-btn {
        height: 48px;
        border-radius: 999px;
        border: 0;
        color: #fff;
        font-weight: 700;
        letter-spacing: 0.2px;
        background: linear-gradient(135deg, #375fc7, #4e7be6);
    }

    .auth-submit-btn:hover {
        color: #fff;
        filter: brightness(.98);
    }

    .auth-social-sep {
        position: relative;
        text-align: center;
        margin: 16px 0 14px;
        color: #64748b;
        font-size: 0.93rem;
    }

    .auth-social-sep::before {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        top: 50%;
        border-top: 1px solid #dbe2ea;
        transform: translateY(-50%);
        z-index: 0;
    }

    .auth-social-sep span {
        position: relative;
        z-index: 1;
        background: #fff;
        padding: 0 10px;
    }

    .auth-social-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .auth-social-btn {
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid #d7dce2;
        color: #1e293b;
        background: #f8fafc;
        text-decoration: none;
        font-weight: 600;
        transition: all .18s ease;
    }

    .auth-social-btn i { font-size: 1.05rem; }

    .auth-social-btn:hover {
        background: #eef3f9;
        color: #0f172a;
        border-color: #c9d4e2;
    }

    .auth-social-icon-box {
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 20px;
        overflow: hidden;
    }

    .auth-social-icon {
        width: 18px;
        height: 18px;
        object-fit: contain;
        object-position: center;
        display: block;
        transform-origin: center;
    }

    .auth-social-icon.is-facebook {
        transform: scale(1.06) translateX(0);
    }

    .auth-social-icon.is-zalo {
        transform: scale(1.18) translateX(1px);
    }

    .auth-social-btn span {
        line-height: 1;
    }

    .auth-social-btn.is-github i { color: #111827; }
</style>

<div class="d-lg-none py-4">
    <div class="auth-mobile card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
        <div class="card-body p-4">
            <h4 class="fw-bold mb-3">Đăng nhập</h4>

            @if ($errors->any())
                <div class="alert alert-danger" style="border-radius: 12px;">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-success" style="border-radius: 12px;">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus autocomplete="username" style="border-radius: 10px;">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required autocomplete="current-password" style="border-radius: 10px;">
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember-mobile-login">
                        <label class="form-check-label" for="remember-mobile-login">Ghi nhớ</label>
                    </div>
                    @if (\Illuminate\Support\Facades\Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="small text-decoration-none">Quên mật khẩu?</a>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius: 999px; height:44px;">Đăng nhập</button>
            </form>
        </div>
    </div>
</div>

<div class="d-none d-lg-block auth-desktop-wrap">
    <div class="auth-desktop-card shadow-sm">
        <div class="row g-0">
            <div class="col-lg-6 auth-brand-panel">
                <div class="auth-brand-inner">
                   
                    <a href="{{ route('register') }}" class="auth-brand-btn">Đăng ký</a>
                </div>
            </div>

            <div class="col-lg-6 auth-form-panel">
                <div class="auth-form-box">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h3 class="fw-bold mb-0">Đăng nhập</h3>
                        <a href="{{ route('home') }}" class="text-decoration-none">Về trang chủ</a>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger" style="border-radius: 12px;">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="alert alert-success" style="border-radius: 12px;">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control auth-form-control" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="Nhập email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mật khẩu</label>
                            <input type="password" name="password" class="form-control auth-form-control" required autocomplete="current-password" placeholder="Nhập mật khẩu">
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember-desktop-login">
                                <label class="form-check-label" for="remember-desktop-login">Ghi nhớ</label>
                            </div>
                            @if (\Illuminate\Support\Facades\Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="small text-decoration-none">Quên mật khẩu?</a>
                            @endif
                        </div>

                        <button type="submit" class="btn auth-submit-btn w-100">Đăng nhập</button>
                    </form>

                    <div class="auth-social-sep"><span>Hoặc đăng nhập với</span></div>
                    <div class="auth-social-grid mb-3">
                        <a href="{{ route('social.redirect', 'google') }}" class="auth-social-btn is-google" title="Google">
                            <span class="auth-social-icon-box"><img src="{{ asset('images/icons8-google-48.png') }}" alt="Google" class="auth-social-icon is-google"></span>
                            <span>Google</span>
                        </a>
                        <a href="{{ route('social.redirect', 'facebook') }}" class="auth-social-btn is-facebook" title="Facebook">
                            <span class="auth-social-icon-box"><img src="{{ asset('images/icons8-fb.gif') }}" alt="Facebook" class="auth-social-icon is-facebook"></span>
                            <span>Facebook</span>
                        </a>
                        <a href="#" class="auth-social-btn is-zalo" title="Zalo">
                            <span class="auth-social-icon-box"><img src="{{ asset('images/icons8-zalo-48.png') }}" alt="Zalo" class="auth-social-icon is-zalo"></span>
                            <span>Zalo</span>
                        </a>
                        <a href="#" class="auth-social-btn is-github" title="GitHub">
                            <span class="auth-social-icon-box"><i class="bi bi-github"></i></span>
                            <span>GitHub</span>
                        </a>

                    </div>

                    <div class="text-center">
                        <span class="text-muted">Chưa có tài khoản?</span>
                        <a href="{{ route('register') }}" class="fw-bold text-decoration-none">Đăng ký</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
