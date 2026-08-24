<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class TextareaField extends AbstractStringField
{
    public function handle(): string
    {
        return 'textarea';
    }
}
