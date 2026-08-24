<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Navigations\CreateNavigation;

final class MakeNavigationCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-navigation {handle} {--name=}';

    protected $description = 'Create a Navigation';

    public function handle(CreateNavigation $action): int
    {
        $handle = $this->stringArgument('handle');
        $navigation = $action->execute([
            'handle' => $handle,
            'name' => $this->option('name') ?: Str::headline($handle),
        ]);
        $this->components->success("Navigation [{$navigation->handle}] created.");

        return self::SUCCESS;
    }
}
