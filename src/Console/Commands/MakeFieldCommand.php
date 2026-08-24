<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Blueprints\CreateBlueprintField;
use LaraCeemes\Models\Blueprint;

final class MakeFieldCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-field {handle} {--blueprint=} {--type=text} {--label=}';

    protected $description = 'Create a Blueprint Field';

    public function handle(CreateBlueprintField $action): int
    {
        $handle = $this->stringArgument('handle');
        $blueprintHandle = $this->stringOptionOrAsk('blueprint', 'Blueprint handle');
        $blueprint = Blueprint::query()->where('handle', $blueprintHandle)->firstOrFail();
        $field = $action->execute($blueprint, [
            'handle' => $handle,
            'label' => $this->option('label') ?: Str::headline($handle),
            'type' => $this->stringOption('type') ?? 'text',
        ]);

        $this->components->success("Field [{$field->handle}] created.");

        return self::SUCCESS;
    }
}
