<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Sections\CreateSectionType;

final class MakeSectionTypeCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-section {handle} {--name=}';

    protected $description = 'Create a Section Type';

    public function handle(CreateSectionType $action): int
    {
        $handle = $this->stringArgument('handle');
        $sectionType = $action->execute([
            'handle' => $handle,
            'name' => $this->option('name') ?: Str::headline($handle),
        ]);
        $this->components->success("Section Type [{$sectionType->handle}] created.");

        return self::SUCCESS;
    }
}
