<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\SectionType;

final class DeleteSectionType extends Action
{
    public function execute(SectionType $sectionType): void
    {
        if ($sectionType->sections()->exists()) {
            throw ValidationException::withMessages([
                'section_type' => "Section Type [{$sectionType->handle}] is still used by Content.",
            ]);
        }

        $this->transaction(fn () => $sectionType->delete());
    }
}
