<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Support\Str;
use LaraCeemes\Actions\Sections\CreateSectionField;
use LaraCeemes\Models\SectionType;

final class MakeSectionFieldCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-section-field {handle} {--section=} {--type=text} {--label=}';

    protected $description = 'Create a field on a Section Type';

    public function handle(CreateSectionField $action): int
    {
        $handle = $this->stringArgument('handle');
        $sectionHandle = $this->stringOptionOrAsk('section', 'Section Type handle');
        $sectionType = SectionType::query()->where('handle', $sectionHandle)->firstOrFail();
        $field = $action->execute($sectionType, [
            'handle' => $handle,
            'label' => $this->option('label') ?: Str::headline($handle),
            'type' => $this->stringOption('type') ?? 'text',
        ]);
        $this->components->success("Section Field [{$field->handle}] created.");

        return self::SUCCESS;
    }
}
