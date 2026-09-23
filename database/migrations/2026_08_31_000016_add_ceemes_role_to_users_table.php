<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! config('ceemes.users.manage_role_column', true)) {
            return;
        }

        $table = (string) config('ceemes.users.table', 'users');
        $roleAttribute = (string) config('ceemes.users.role_attribute', 'role');

        if (Schema::hasColumn($table, $roleAttribute)) {
            throw new RuntimeException(
                "Column [{$table}.{$roleAttribute}] already exists. Set CEEMES_MANAGE_USER_ROLE_COLUMN=false before migrating so the application keeps ownership of it.",
            );
        }

        Schema::table($table, function (Blueprint $blueprint) use ($roleAttribute): void {
            $blueprint->string($roleAttribute, 50)
                ->default((string) config('ceemes.users.default_role', 'user'))
                ->index();
        });
    }

    public function down(): void
    {
        if (! config('ceemes.users.manage_role_column', true)) {
            return;
        }

        $table = (string) config('ceemes.users.table', 'users');
        $roleAttribute = (string) config('ceemes.users.role_attribute', 'role');

        if (Schema::hasColumn($table, $roleAttribute)) {
            Schema::table($table, function (Blueprint $blueprint) use ($roleAttribute): void {
                $blueprint->dropColumn($roleAttribute);
            });
        }
    }
};
