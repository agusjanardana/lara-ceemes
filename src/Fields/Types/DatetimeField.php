<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class DatetimeField extends AbstractStringField
{
    public function handle(): string
    {
        return 'datetime';
    }

    public function rules(array $config = []): array
    {
        return [$this->presenceRule($config), 'date'];
    }
}
