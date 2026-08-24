<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use LaraCeemes\Models\Entry;
use LaraCeemes\Support\SeoData;

final class SeoManager
{
    public function __construct(private readonly SettingManager $settings) {}

    public function global(): SeoData
    {
        return new SeoData(
            title: $this->string('seo.default_meta_title') ?? $this->string('seo.site_title'),
            description: $this->string('seo.default_meta_description'),
            ogImage: $this->string('seo.default_og_image'),
            twitterCard: $this->string('seo.twitter_card'),
            robotsIndex: (bool) $this->settings->get('seo.default_robots_index', true),
            robotsFollow: (bool) $this->settings->get('seo.default_robots_follow', true),
            canonicalUrl: $this->string('seo.canonical_base_url'),
            siteTitle: $this->string('seo.site_title'),
            titleSeparator: $this->string('seo.title_separator') ?? '|',
        );
    }

    public function forEntry(Entry $entry): SeoData
    {
        $global = $this->global();
        $entrySeo = $entry->seo();

        return new SeoData(
            title: $this->nullableString($entrySeo['title'] ?? null) ?? $global->title,
            description: $this->nullableString($entrySeo['description'] ?? null) ?? $global->description,
            ogImage: $this->nullableString($entrySeo['og_image'] ?? null) ?? $global->ogImage,
            twitterCard: $global->twitterCard,
            robotsIndex: isset($entrySeo['robots_index']) ? (bool) $entrySeo['robots_index'] : $global->robotsIndex,
            robotsFollow: isset($entrySeo['robots_follow']) ? (bool) $entrySeo['robots_follow'] : $global->robotsFollow,
            canonicalUrl: $this->nullableString($entrySeo['canonical_url'] ?? null) ?? $global->canonicalUrl,
            siteTitle: $global->siteTitle,
            titleSeparator: $global->titleSeparator,
        );
    }

    private function string(string $key): ?string
    {
        return $this->nullableString($this->settings->get($key));
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
