<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private function isSuperAdmin(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $allowed = (string) env('SUPER_ADMIN_EMAIL', '');
        $allowed = trim(strtolower($allowed));

        if ($allowed === '') {
            return Permission::allows($user, 'super.admin') || Permission::allows($user, 'admin.access');
        }

        return strtolower((string) $user->email) === $allowed;
    }

    private function denyNotEnoughLevel()
    {
        return redirect()->route('admin.dashboard')->with('error', 'Xin lỗi, tài khoản này chưa đủ cấp độ để vào.');
    }

    public function index(Request $request)
    {
        if (!$this->isSuperAdmin(Auth::user()) && !Permission::allows(Auth::user(), 'users.manage') && !Permission::allows(Auth::user(), 'users.view')) {
            return $this->denyNotEnoughLevel();
        }

        $query = User::query();

        if ($request->filled('q')) {
            $q = trim((string) $request->get('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $users = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        if (!$this->isSuperAdmin(Auth::user())) {
            return $this->denyNotEnoughLevel();
        }

        $validated = $request->validate([
            'role' => ['required', 'in:admin,user,agent'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string'],
        ]);

        if (Auth::id() === $user->id) {
            return back()->with('error', 'Bạn không thể thay đổi quyền của chính mình!');
        }

        $before = $user->only(['role', 'email', 'name']);
        $newRole = $validated['role'];

        if ($user->role === 'admin' && $newRole !== 'admin') {
            $adminCount = User::query()->where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Không thể hạ quyền admin cuối cùng!');
            }
        }

        $user->role = $newRole;
        if ($request->has('permissions')) {
            $user->permissions = array_values(array_filter((array) $validated['permissions'], function ($permission) {
                return is_string($permission) && $permission !== '';
            }));
        }
        $user->save();

        $after = $user->fresh()->only(['role', 'permissions', 'email', 'name']);
        ActivityLogger::log('user.update_role', $user, 'Cập nhật quyền người dùng', [
            'before' => $before,
            'after' => $after,
        ], $request);

        return back()->with('success', 'Cập nhật quyền người dùng thành công!');
    }
}
