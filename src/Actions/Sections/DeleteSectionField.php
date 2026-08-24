<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use LaraCeemes\Actions\Action;
use LaraCeemes\Models\SectionField;

final class DeleteSectionField extends Action
{
    public function execute(SectionField $field): void
    {
        $this->transaction(fn () => $field->delete());
    }
}
