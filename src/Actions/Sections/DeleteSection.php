<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SectionDeleted;
use LaraCeemes\Exceptions\StructureInUse;
use LaraCeemes\Models\Section;

final class DeleteSection extends Action
{
    public function execute(Section $section): void
    {
        if ($section->contents()->exists()) {
            throw StructureInUse::section((string) ($section->handle ?: $section->uuid));
        }

        $this->transaction(function () use ($section): void {
            $section->delete();
            event(new SectionDeleted($section));
        });
    }
}
