<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Illuminate\Validation\ValidationException;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\SectionType;

final class AllowedSectionValidator
{
    public function validate(Content $content, SectionType $sectionType, string $fieldHandle): void
    {
        $field = $content->set->fields()
            ->where('handle', $fieldHandle)
            ->where('type', 'sections')
            ->first();

        $allowed = is_array($field?->config) && is_array($field->config['allowed'] ?? null)
            ? $field->config['allowed']
            : [];

        if ($field === null) {
            return;
        }

        if (! in_array($sectionType->handle, $allowed, true)) {
            throw ValidationException::withMessages([
                'section_type' => "Section Type [{$sectionType->handle}] is not allowed in [{$fieldHandle}].",
            ]);
        }
    }
}
