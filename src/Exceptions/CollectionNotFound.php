<?php

declare(strict_types=1);

namespace LaraCeemes\Exceptions;

use RuntimeException;

final class CollectionNotFound extends RuntimeException
{
    public static function forHandle(string $handle): self
    {
        return new self("Collection [{$handle}] does not exist.");
    }
}
