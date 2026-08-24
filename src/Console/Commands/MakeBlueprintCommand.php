<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Blueprints\CreateBlueprint;
use LaraCeemes\Managers\CollectionManager;

final class MakeBlueprintCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-blueprint {handle} {--collection=} {--name=}';

    protected $description = 'Create a Blueprint inside a Collection';

    public function handle(CreateBlueprint $action, CollectionManager $collections): int
    {
        $handle = $this->stringArgument('handle');
        $collectionHandle = $this->stringOptionOrAsk('collection', 'Collection handle');
        $blueprint = $action->execute($collections->get($collectionHandle), [
            'name' => $this->option('name') ?: Str::headline($handle),
            'handle' => $handle,
        ]);

        $this->components->success("Blueprint [{$blueprint->handle}] created.");

        return self::SUCCESS;
    }
}
