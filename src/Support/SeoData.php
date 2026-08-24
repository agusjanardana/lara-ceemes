<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

final readonly class SeoData
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $ogImage = null,
        public ?string $twitterCard = null,
        public bool $robotsIndex = true,
        public bool $robotsFollow = true,
        public ?string $canonicalUrl = null,
        public ?string $siteTitle = null,
        public string $titleSeparator = '|',
    ) {}

    /** @return array<string, bool|string|null> */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'og_image' => $this->ogImage,
            'twitter_card' => $this->twitterCard,
            'robots_index' => $this->robotsIndex,
            'robots_follow' => $this->robotsFollow,
            'canonical_url' => $this->canonicalUrl,
            'site_title' => $this->siteTitle,
            'title_separator' => $this->titleSeparator,
        ];
    }
}
