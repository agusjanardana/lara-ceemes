<?php

declare(strict_types=1);

namespace LaraCeemes\Exceptions;

use RuntimeException;

final class StructureInUse extends RuntimeException
{
    public static function set(string $handle): self
    {
        return new self("Set [{$handle}] cannot be deleted while it contains Content.");
    }

    public static function section(string $handle): self
    {
        return new self("Section [{$handle}] cannot be deleted while it is attached to Content.");
    }
}
