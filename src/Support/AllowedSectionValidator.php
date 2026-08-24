<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Illuminate\Validation\ValidationException;
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\SectionType;

final class AllowedSectionValidator
{
    public function validate(Entry $entry, SectionType $sectionType, string $fieldHandle): void
    {
        $field = $entry->blueprint->fields()
            ->where('handle', $fieldHandle)
            ->where('type', 'sections')
            ->first();

        $allowed = is_array($field?->config) && is_array($field->config['allowed'] ?? null)
            ? $field->config['allowed']
            : [];

        if (! in_array($sectionType->handle, $allowed, true)) {
            throw ValidationException::withMessages([
                'section_type' => "Section Type [{$sectionType->handle}] is not allowed in [{$fieldHandle}].",
            ]);
        }
    }
}
