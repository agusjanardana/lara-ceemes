<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Schema;
use LaraCeemes\Models\SuperAdmin;

final class SuperAdminRegistry
{
    public function contains(mixed $user): bool
    {
        if (! $user instanceof Authenticatable || ! Schema::hasTable('ceemes_super_admins')) {
            return false;
        }

        $key = $user->getAuthIdentifier();

        return $key !== null
            && SuperAdmin::query()->where('user_key', $key)->exists();
    }

    public function promote(Authenticatable $user): SuperAdmin
    {
        return SuperAdmin::query()->firstOrCreate([
            'user_key' => $user->getAuthIdentifier(),
        ]);
    }
}
