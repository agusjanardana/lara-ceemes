<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Sets\CreateSet;

final class MakeSetCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-set {handle} {--name=} {--route=} {--template=} {--no-template : Create a data-only Set without frontend routing}';

    protected $description = 'Create a Lara Ceemes Set';

    public function handle(CreateSet $action): int
    {
        $handle = $this->stringArgument('handle');
        $set = $action->execute([
            'name' => $this->option('name') ?: Str::headline($handle),
            'handle' => $handle,
            'route' => $this->option('route'),
            'template' => $this->option('template'),
            'template_mode' => (bool) $this->option('no-template')
                ? 'none'
                : ($this->option('template') ? 'custom' : 'auto'),
        ]);

        $this->components->success("Set [{$set->handle}] created.");
        $this->components->twoColumnDetail('Route', $set->route ?: 'Data only');
        $this->components->twoColumnDetail('Blade', $set->template ?: 'None');

        return self::SUCCESS;
    }
}
