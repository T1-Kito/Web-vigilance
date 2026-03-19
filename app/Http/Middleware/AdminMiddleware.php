<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra user đã đăng nhập chưa
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Kiểm tra user có role admin không
        if (auth()->user()->role !== 'admin') {
            // Redirect user thường về trang chủ với thông báo
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập trang này!');
        }

        return $next($request);
    }
}
