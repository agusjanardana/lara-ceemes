<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SectionUpdated;
use LaraCeemes\Models\Section;
use LaraCeemes\Support\ContentCacheInvalidator;

final class SetSectionEnabled extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(Section $section, bool $enabled): Section
    {
        return $this->transaction(function () use ($section, $enabled): Section {
            $section->update(['is_enabled' => $enabled]);
            $section->refresh();
            $this->cache->entry($section->entry);
            event(new SectionUpdated($section));

            return $section;
        });
    }
}
