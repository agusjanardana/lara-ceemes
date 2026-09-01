<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use LaraCeemes\Models\Content;
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

    public function forContent(Content $content): SeoData
    {
        $global = $this->global();
        $contentSeo = $content->seo();

        return new SeoData(
            title: $this->nullableString($contentSeo['title'] ?? null) ?? $global->title,
            description: $this->nullableString($contentSeo['description'] ?? null) ?? $global->description,
            ogImage: $this->nullableString($contentSeo['og_image'] ?? null) ?? $global->ogImage,
            twitterCard: $global->twitterCard,
            robotsIndex: isset($contentSeo['robots_index']) ? (bool) $contentSeo['robots_index'] : $global->robotsIndex,
            robotsFollow: isset($contentSeo['robots_follow']) ? (bool) $contentSeo['robots_follow'] : $global->robotsFollow,
            canonicalUrl: $this->nullableString($contentSeo['canonical_url'] ?? null) ?? $global->canonicalUrl,
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
