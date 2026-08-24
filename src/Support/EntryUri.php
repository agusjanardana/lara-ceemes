<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use LaraCeemes\Models\Collection;

final class EntryUri
{
    public function normalize(mixed $value, Collection $collection, string $slug): string
    {
        $uri = is_string($value) ? trim($value) : '';

        if ($uri === '') {
            $pattern = is_string($collection->route) && str_contains($collection->route, '{slug}')
                ? $collection->route
                : '/{slug}';
            $uri = str_replace('{slug}', $slug, $pattern);
        }

        $uri = '/'.trim($uri, '/');

        return $uri;
    }
}
