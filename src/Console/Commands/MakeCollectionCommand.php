<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Collections\CreateCollection;

final class MakeCollectionCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-collection {handle} {--name=} {--route=}';

    protected $description = 'Create a Lara Ceemes Collection';

    public function handle(CreateCollection $action): int
    {
        $handle = $this->stringArgument('handle');
        $collection = $action->execute([
            'name' => $this->option('name') ?: Str::headline($handle),
            'handle' => $handle,
            'route' => $this->option('route'),
        ]);

        $this->components->success("Collection [{$collection->handle}] created.");

        return self::SUCCESS;
    }
}
