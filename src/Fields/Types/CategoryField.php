<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class CategoryField extends AbstractRelationField
{
    public function handle(): string
    {
        return 'category';
    }

    protected function allowsMultiple(array $config): bool
    {
        return ($config['multiple'] ?? true) === true;
    }
}
