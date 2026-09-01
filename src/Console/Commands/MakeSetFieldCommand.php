<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Sets\CreateSetField;
use LaraCeemes\Models\Set;

final class MakeSetFieldCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-set-field {handle} {--set=} {--type=text} {--label=}';

    protected $description = 'Create a Fixed Field directly on a Set';

    public function handle(CreateSetField $action): int
    {
        $handle = $this->stringArgument('handle');
        $setHandle = $this->stringOptionOrAsk('set', 'Set handle');
        $set = Set::query()->where('handle', $setHandle)->firstOrFail();
        $field = $action->execute($set, [
            'handle' => $handle,
            'label' => $this->option('label') ?: Str::headline($handle),
            'type' => $this->stringOption('type') ?? 'text',
        ]);

        $this->components->success("Set Field [{$field->handle}] created.");

        return self::SUCCESS;
    }
}
