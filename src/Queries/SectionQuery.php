<?php

declare(strict_types=1);

namespace LaraCeemes\Queries;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Section;

final class SectionQuery
{
    /** @var BelongsToMany<Section, Content> */
    private BelongsToMany $query;

    public function __construct(Content $content, string $region)
    {
        $this->query = $content->placedSections()
            ->wherePivot('region', $region)
            ->orderByPivot('sort_order');
    }

    public function enabled(): self
    {
        $this->query->wherePivot('is_enabled', true);

        return $this;
    }

    /** @return EloquentCollection<int, Section> */
    public function get(): EloquentCollection
    {
        return $this->query->get();
    }
}
