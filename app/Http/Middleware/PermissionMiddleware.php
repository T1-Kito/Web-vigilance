<?php

namespace App\Http\Middleware;

use App\Support\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!Permission::allows(auth()->user(), $permission)) {
            return redirect()->back()->withInput()->with('error', 'Bạn không có quyền truy cập chức năng này!');
        }

        return $next($request);
    }
}
