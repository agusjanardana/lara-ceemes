<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Navigations\CreateNavigation;
use LaraCeemes\Support\SiteContext;

final class MakeNavigationCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-navigation {handle} {--name=} {--site= : Site handle}';

    protected $description = 'Create a Navigation';

    public function handle(CreateNavigation $action, SiteContext $sites): int
    {
        if ($site = $this->stringOption('site')) {
            $sites->useHandle($site, enabledOnly: true);
        }
        $handle = $this->stringArgument('handle');
        $navigation = $action->execute([
            'handle' => $handle,
            'name' => $this->option('name') ?: Str::headline($handle),
        ]);
        $this->components->success("Navigation [{$navigation->handle}] created.");

        return self::SUCCESS;
    }
}
