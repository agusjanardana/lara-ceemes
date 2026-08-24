<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use LaraCeemes\Models\Entry;
use LaraCeemes\Models\NavigationItem;
use LaraCeemes\Models\Section;
use LaraCeemes\Models\Setting;

final class MediaUsageDetector
{
    /** @return array<int, MediaUsage> */
    public function forUuid(string $mediaUuid): array
    {
        return [
            ...$this->entryUsages($mediaUuid),
            ...$this->sectionUsages($mediaUuid),
            ...$this->settingUsages($mediaUuid),
            ...$this->navigationUsages($mediaUuid),
        ];
    }

    /** @return array<int, MediaUsage> */
    private function entryUsages(string $mediaUuid): array
    {
        $usages = [];

        foreach (Entry::query()->with('collection')->get() as $entry) {
            foreach ($entry->data() as $fieldHandle => $value) {
                if ($this->contains($value, $mediaUuid)) {
                    $usages[] = new MediaUsage(
                        'entry',
                        $entry->uuid,
                        "{$entry->collection->name} / {$entry->title}",
                        (string) $fieldHandle,
                    );
                }
            }

            if ($this->contains($entry->seo(), $mediaUuid)) {
                $usages[] = new MediaUsage(
                    'entry_seo',
                    $entry->uuid,
                    "{$entry->collection->name} / {$entry->title} / SEO",
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

        foreach (Section::query()->with(['entry.collection', 'sectionType'])->get() as $section) {
            foreach ($section->data() as $fieldHandle => $value) {
                if ($this->contains($value, $mediaUuid)) {
                    $usages[] = new MediaUsage(
                        'section',
                        $section->uuid,
                        "{$section->entry->collection->name} / {$section->entry->title} / {$section->sectionType->name}",
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
