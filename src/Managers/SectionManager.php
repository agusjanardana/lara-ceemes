<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use LaraCeemes\Models\Content;
use LaraCeemes\Models\Section;
use LaraCeemes\Queries\SectionQuery;

final class SectionManager extends Manager
{
    public function find(string $handle): ?Section
    {
        return Section::query()->where('handle', $handle)->first();
    }

    public function forContent(Content $content, string $region = 'sections'): SectionQuery
    {
        return new SectionQuery($content, $region);
    }
}
