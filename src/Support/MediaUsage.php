<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

final readonly class MediaUsage
{
    public function __construct(
        public string $sourceType,
        public string $sourceUuid,
        public string $label,
        public string $fieldHandle,
    ) {}

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'source_type' => $this->sourceType,
            'source_uuid' => $this->sourceUuid,
            'label' => $this->label,
            'field_handle' => $this->fieldHandle,
        ];
    }
}
