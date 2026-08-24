<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class DateField extends AbstractStringField
{
    public function handle(): string
    {
        return 'date';
    }

    public function rules(array $config = []): array
    {
        return [$this->presenceRule($config), 'date_format:Y-m-d'];
    }
}
