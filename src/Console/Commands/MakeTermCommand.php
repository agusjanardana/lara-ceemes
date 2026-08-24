<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Taxonomies\CreateTerm;
use LaraCeemes\Models\Taxonomy;

final class MakeTermCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-term {slug} {--taxonomy=} {--name=} {--parent=}';

    protected $description = 'Create a Term in a Taxonomy';

    public function handle(CreateTerm $action): int
    {
        $slug = $this->stringArgument('slug');
        $taxonomyHandle = $this->stringOptionOrAsk('taxonomy', 'Taxonomy handle');
        $taxonomy = Taxonomy::query()->where('handle', $taxonomyHandle)->firstOrFail();
        $data = [
            'slug' => $slug,
            'name' => $this->option('name') ?: Str::headline($slug),
        ];

        if ($this->option('parent')) {
            $data['parent_uuid'] = $taxonomy->terms()->where('slug', $this->option('parent'))->value('uuid');
        }

        $term = $action->execute($taxonomy, $data);
        $this->components->success("Term [{$term->slug}] created.");

        return self::SUCCESS;
    }
}
