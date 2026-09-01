<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use LaraCeemes\Models\Content;
use LaraCeemes\Models\NavigationItem;
use LaraCeemes\Models\Section;
use LaraCeemes\Models\Setting;

final class MediaUsageDetector
{
    /** @return array<int, MediaUsage> */
    public function forUuid(string $mediaUuid): array
    {
        return [
            ...$this->contentUsages($mediaUuid),
            ...$this->sectionUsages($mediaUuid),
            ...$this->settingUsages($mediaUuid),
            ...$this->navigationUsages($mediaUuid),
        ];
    }

    /** @return array<int, MediaUsage> */
    private function contentUsages(string $mediaUuid): array
    {
        $usages = [];

        foreach (Content::query()->with('set')->get() as $content) {
            foreach ($content->data() as $fieldHandle => $value) {
                if ($this->contains($value, $mediaUuid)) {
                    $usages[] = new MediaUsage(
                        'content',
                        $content->uuid,
                        "{$content->set->name} / {$content->title}",
                        (string) $fieldHandle,
                    );
                }
            }

            if ($this->contains($content->seo(), $mediaUuid)) {
                $usages[] = new MediaUsage(
                    'content_seo',
                    $content->uuid,
                    "{$content->set->name} / {$content->title} / SEO",
                    'seo',
                );
            }
        }

        return $usages;
    }

    /** @return array<int, MediaUsage> */
    private function sectionUsages(string $mediaUuid): array
    {
        $usages = [];

        foreach (Section::query()->with(['contents.set', 'sectionType'])->get() as $section) {
            foreach ($section->data() as $fieldHandle => $value) {
                if ($this->contains($value, $mediaUuid)) {
                    $usages[] = new MediaUsage(
                        'section',
                        $section->uuid,
                        ($section->name ?: $section->handle)." / {$section->sectionType->name} / used by {$section->contents->count()} content(s)",
                        (string) $fieldHandle,
                    );
                }
            }
        }

        return $usages;
    }

    /** @return array<int, MediaUsage> */
    private function settingUsages(string $mediaUuid): array
    {
        $usages = [];

        foreach (Setting::query()->get() as $setting) {
            if ($this->contains($setting->value, $mediaUuid)) {
                $usages[] = new MediaUsage(
                    'setting',
                    $setting->uuid,
                    "Setting {$setting->group}.{$setting->key}",
                    "{$setting->group}.{$setting->key}",
                );
            }
        }

        return $usages;
    }

    /** @return array<int, MediaUsage> */
    private function navigationUsages(string $mediaUuid): array
    {
        $usages = [];

        foreach (NavigationItem::query()->with('navigation')->get() as $item) {
            if ($this->contains($item->data, $mediaUuid)) {
                $usages[] = new MediaUsage(
                    'navigation',
                    $item->uuid,
                    "Navigation {$item->navigation->name} / {$item->label}",
                    'data',
                );
            }
        }

        return $usages;
    }

    private function contains(mixed $value, string $mediaUuid): bool
    {
        if (is_string($value)) {
            return $value === $mediaUuid;
        }

        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $nestedValue) {
            if ($this->contains($nestedValue, $mediaUuid)) {
                return true;
            }
        }

        return false;
    }
}
