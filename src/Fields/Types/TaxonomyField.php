<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class TaxonomyField extends AbstractRelationField
{
    public function handle(): string
    {
        return 'taxonomy';
    }

    protected function allowsMultiple(array $config): bool
    {
        return ($config['multiple'] ?? true) === true;
    }
}
