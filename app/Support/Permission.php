<?php

namespace App\Support;

use App\Models\Permission as PermissionModel;
use App\Models\User;

class Permission
{
    public static function defaultsForRole(?string $role): array
    {
        return match ($role) {
            'admin' => ['admin.access'],
            default => [],
        };
    }

    public static function userPermissions(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $permissions = $user->permissions ?? [];
        if (!is_array($permissions)) {
            $permissions = [];
        }

        $rolePermissions = self::defaultsForRole((string) $user->role);

        return array_values(array_unique(array_merge($rolePermissions, $permissions)));
    }

    public static function allows(?User $user, string $permission): bool
    {
        $permissions = self::userPermissions($user);

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }

    public static function groupsWithPermissions()
    {
        return \App\Models\PermissionGroup::query()
            ->with(['permissions' => function ($query) {
                $query->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public static function allPermissionKeys(): array
    {
        return PermissionModel::query()->pluck('slug')->all();
    }
}
