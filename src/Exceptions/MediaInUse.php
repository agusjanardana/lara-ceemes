<?php

declare(strict_types=1);

namespace LaraCeemes\Exceptions;

use LaraCeemes\Support\MediaUsage;
use RuntimeException;

final class MediaInUse extends RuntimeException
{
    /** @param array<int, MediaUsage> $usages */
    public function __construct(public readonly array $usages)
    {
        parent::__construct('Media cannot be deleted while it is still used by content.');
    }
}
