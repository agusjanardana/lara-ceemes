<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

final class UserRoleAuthorizer
{
    public function allows(mixed $user): bool
    {
        if (! $user instanceof Authenticatable || ! $user instanceof Model) {
            return false;
        }

        $attribute = (string) config('ceemes.users.role_attribute', 'role');
        $requiredRole = (string) config('ceemes.users.superadmin_role', 'superadmin');

        return hash_equals($requiredRole, (string) $user->getAttribute($attribute));
    }
}
