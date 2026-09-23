<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LaraCeemes\Models\CategoryGroup;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Media;
use LaraCeemes\Models\Set;

final class StatusCommand extends Command
{
    protected $signature = 'ceemes:status';

    protected $description = 'Display the current Lara Ceemes installation status';

    public function handle(): int
    {
        $databaseConnected = true;

        try {
            DB::connection()->getPdo();
        } catch (\Throwable) {
            $databaseConnected = false;
        }

        $installed = Schema::hasTable('ceemes_sets');
        $this->components->twoColumnDetail('Database', $databaseConnected ? '<fg=green>Connected</>' : '<fg=red>Unavailable</>');
        $this->components->twoColumnDetail('Migrations', $installed ? '<fg=green>Installed</>' : '<fg=yellow>Not installed</>');
        $this->components->twoColumnDetail('Cache', config('ceemes.cache.enabled') ? 'Enabled' : 'Disabled');

        if ($installed) {
            $this->components->twoColumnDetail('Sets', (string) Set::query()->count());
            $this->components->twoColumnDetail('Contents', (string) Content::withTrashed()->count());
            $this->components->twoColumnDetail('Category Groups', (string) CategoryGroup::query()->count());
            $this->components->twoColumnDetail('Media', (string) Media::withTrashed()->count());

            $usersTable = (string) config('ceemes.users.table', 'users');
            $roleAttribute = (string) config('ceemes.users.role_attribute', 'role');

            if (Schema::hasTable($usersTable) && Schema::hasColumn($usersTable, $roleAttribute)) {
                $this->components->twoColumnDetail(
                    'Superadmins',
                    (string) DB::table($usersTable)
                        ->where($roleAttribute, config('ceemes.users.superadmin_role', 'superadmin'))
                        ->count(),
                );
            }
        }

        return $databaseConnected ? self::SUCCESS : self::FAILURE;
    }
}
