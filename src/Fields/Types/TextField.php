<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class TextField extends AbstractStringField
{
    public function handle(): string
    {
        return 'text';
    }

    public function rules(array $config = []): array
    {
        $config['max_length'] ??= 255;

        return parent::rules($config);
    }
}
