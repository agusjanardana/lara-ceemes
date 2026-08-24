<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

final class SeoField extends AbstractArrayField
{
    public function handle(): string
    {
        return 'seo';
    }
}
