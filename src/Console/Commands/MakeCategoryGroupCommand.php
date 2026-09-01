<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Categories\CreateCategoryGroup;

final class MakeCategoryGroupCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-category-group {handle} {--name=}';

    protected $description = 'Create a Lara Ceemes Category Group';

    public function handle(CreateCategoryGroup $action): int
    {
        $handle = $this->stringArgument('handle');
        $group = $action->execute(['handle' => $handle, 'name' => $this->option('name') ?: Str::headline($handle)]);
        $this->components->success("Category Group [{$group->handle}] created.");

        return self::SUCCESS;
    }
}
