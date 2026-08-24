<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Taxonomies\CreateTaxonomy;

final class MakeTaxonomyCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-taxonomy {handle} {--name=}';

    protected $description = 'Create a Taxonomy';

    public function handle(CreateTaxonomy $action): int
    {
        $handle = $this->stringArgument('handle');
        $taxonomy = $action->execute([
            'handle' => $handle,
            'name' => $this->option('name') ?: Str::headline($handle),
        ]);
        $this->components->success("Taxonomy [{$taxonomy->handle}] created.");

        return self::SUCCESS;
    }
}
