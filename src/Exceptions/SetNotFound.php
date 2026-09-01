<?php

declare(strict_types=1);

namespace LaraCeemes\Exceptions;

use RuntimeException;

final class SetNotFound extends RuntimeException
{
    public static function forHandle(string $handle): self
    {
        return new self("Set [{$handle}] does not exist.");
    }
}
