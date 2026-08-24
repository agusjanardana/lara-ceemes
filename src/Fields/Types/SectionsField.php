<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class SectionsField extends AbstractArrayField
{
    public function handle(): string
    {
        return 'sections';
    }
}
