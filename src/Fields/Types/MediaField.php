<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

use LaraCeemes\Managers\MediaManager;

final class MediaField extends AbstractRelationField
{
    public function __construct(private readonly MediaManager $media) {}

    public function handle(): string
    {
        return 'media';
    }

    protected function allowsMultiple(array $config): bool
    {
        return ($config['multiple'] ?? false) === true || (int) ($config['max_files'] ?? 1) !== 1;
    }

    public function resolve(mixed $value, array $config = []): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($this->allowsMultiple($config)) {
            return array_values(array_filter(array_map(
                fn (mixed $uuid) => is_string($uuid) ? $this->media->find($uuid) : null,
                (array) $value,
            )));
        }

        return is_string($value) ? $this->media->find($value) : null;
    }
}
