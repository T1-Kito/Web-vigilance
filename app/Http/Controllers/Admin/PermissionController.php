<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function edit(User $user)
    {
        if (!Permission::allows(auth()->user(), 'admin.access')) {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập chức năng này!');
        }

        $permissionGroups = Permission::groupsWithPermissions();

        return view('admin.users.permissions', compact('user', 'permissionGroups'));
    }

    public function update(Request $request, User $user)
    {
        if (!Permission::allows(auth()->user(), 'admin.access')) {
            return redirect()->route('home')->with('error', 'Bạn không có quyền truy cập chức năng này!');
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        $before = $user->only(['permissions', 'email', 'name']);
        $user->permissions = array_values(array_filter((array) ($validated['permissions'] ?? []), function ($permission) {
            return is_string($permission) && $permission !== '';
        }));
        $user->save();

        ActivityLogger::log('user.update_permissions', $user, 'Cập nhật quyền người dùng', [
            'before' => $before,
            'after' => $user->fresh()->only(['permissions', 'email', 'name']),
        ], $request);

        return back()->with('success', 'Cập nhật quyền thành công!');
    }
}
