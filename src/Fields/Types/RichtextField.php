<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class RichtextField extends AbstractStringField
{
    public function handle(): string
    {
        return 'richtext';
    }
}
