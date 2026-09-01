<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use LaraCeemes\Models\Set;

final class ContentUri
{
    public function normalize(mixed $value, Set $set, string $slug): string
    {
        $uri = is_string($value) ? trim($value) : '';

        if ($uri === '') {
            $pattern = is_string($set->route) && str_contains($set->route, '{slug}')
                ? $set->route
                : '/{slug}';
            $uri = str_replace('{slug}', $slug, $pattern);
        }

        $uri = '/'.trim($uri, '/');

        return $uri;
    }
}
