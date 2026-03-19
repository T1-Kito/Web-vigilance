<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    private function isMobileRequest(Request $request): bool
    {
        $ua = (string) $request->header('User-Agent', '');

        return (bool) preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua);
    }

    /**
     * Display the login view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($this->isMobileRequest($request)) {
            return view('auth.login');
        }

        return redirect()->route('home')->with('showLoginModal', true);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        try {
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Nếu là AJAX request, trả về JSON response
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Email hoặc mật khẩu không đúng',
                    'errors' => $e->errors()
                ], 422);
            }

            if ($this->isMobileRequest($request)) {
                return redirect()->route('login')
                    ->withErrors($e->errors())
                    ->withInput($request->only('email'));
            }

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput($request->only('email'))
                ->with('showLoginModal', true);
        }

        $request->session()->regenerate();

        // Xác định trang đích theo vai trò
        $redirectTo = (auth()->user() && auth()->user()->role === 'admin')
            ? route('admin.products.index')
            : route('home');

        // Nếu là AJAX request, trả về JSON response
        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Đăng nhập thành công!',
                'redirect' => $redirectTo,
            ]);
        }

        // Redirect thông thường
        return redirect()->intended($redirectTo);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
