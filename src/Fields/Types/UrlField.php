<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class UrlField extends AbstractStringField
{
    public function handle(): string
    {
        return 'url';
    }

    public function rules(array $config = []): array
    {
        return [...parent::rules($config), 'url'];
    }
}
