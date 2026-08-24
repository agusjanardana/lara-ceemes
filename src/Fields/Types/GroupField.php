<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class GroupField extends AbstractArrayField
{
    public function handle(): string
    {
        return 'group';
    }
}
