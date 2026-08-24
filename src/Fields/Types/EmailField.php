<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class EmailField extends AbstractStringField
{
    public function handle(): string
    {
        return 'email';
    }

    public function rules(array $config = []): array
    {
        return [...parent::rules($config), 'email'];
    }
}
