<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\Media;
use LaraCeemes\Models\SuperAdmin;
use LaraCeemes\Models\Taxonomy;

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

        $installed = Schema::hasTable('ceemes_collections');
        $this->components->twoColumnDetail('Database', $databaseConnected ? '<fg=green>Connected</>' : '<fg=red>Unavailable</>');
        $this->components->twoColumnDetail('Migrations', $installed ? '<fg=green>Installed</>' : '<fg=yellow>Not installed</>');
        $this->components->twoColumnDetail('Cache', config('ceemes.cache.enabled') ? 'Enabled' : 'Disabled');

        if ($installed) {
            $this->components->twoColumnDetail('Collections', (string) Collection::query()->count());
            $this->components->twoColumnDetail('Entries', (string) Entry::withTrashed()->count());
            $this->components->twoColumnDetail('Taxonomies', (string) Taxonomy::query()->count());
            $this->components->twoColumnDetail('Media', (string) Media::withTrashed()->count());

            if (Schema::hasTable('ceemes_super_admins')) {
                $this->components->twoColumnDetail('Superadmins', (string) SuperAdmin::query()->count());
            }
        }

        return $databaseConnected ? self::SUCCESS : self::FAILURE;
    }
}
