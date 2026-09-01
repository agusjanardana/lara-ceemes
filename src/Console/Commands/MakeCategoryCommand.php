<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Categories\CreateCategory;
use LaraCeemes\Models\CategoryGroup;

final class MakeCategoryCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-category {slug} {--group=} {--name=}';

    protected $description = 'Create a Category in a Category Group';

    public function handle(CreateCategory $action): int
    {
        $slug = $this->stringArgument('slug');
        $groupHandle = $this->stringOptionOrAsk('group', 'Category Group handle');
        $group = CategoryGroup::query()->where('handle', $groupHandle)->firstOrFail();
        $category = $action->execute($group, ['slug' => $slug, 'name' => $this->option('name') ?: Str::headline($slug)]);
        $this->components->success("Category [{$category->slug}] created.");

        return self::SUCCESS;
    }
}
