<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SectionDeleted;
use LaraCeemes\Models\Section;
use LaraCeemes\Support\ContentCacheInvalidator;

final class DeleteSection extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(Section $section): void
    {
        $this->transaction(function () use ($section): void {
            $entry = $section->entry;
            $fieldHandle = $section->field_handle;
            $sortOrder = $section->sort_order;
            $section->delete();

            $entry->sectionItems()
                ->where('field_handle', $fieldHandle)
                ->where('sort_order', '>', $sortOrder)
                ->decrement('sort_order');

            $this->cache->entry($entry);
            event(new SectionDeleted($section));
        });
    }
}
