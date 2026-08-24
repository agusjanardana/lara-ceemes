<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class BooleanField extends AbstractFieldType
{
    public function handle(): string
    {
        return 'boolean';
    }

    public function rules(array $config = []): array
    {
        return [$this->presenceRule($config), 'boolean'];
    }

    public function normalize(mixed $value, array $config = []): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOL);
    }
}
