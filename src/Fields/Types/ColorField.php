<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class ColorField extends AbstractStringField
{
    public function handle(): string
    {
        return 'color';
    }

    public function rules(array $config = []): array
    {
        return [$this->presenceRule($config), 'regex:/^#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/'];
    }
}
