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
        $rules = [$this->presenceRule($config), 'date'];

        if (isset($config['after_or_equal'])) {
            $rules[] = 'after_or_equal:'.$config['after_or_equal'];
        }

        if (isset($config['before_or_equal'])) {
            $rules[] = 'before_or_equal:'.$config['before_or_equal'];
        }

        return $rules;
    }
}
