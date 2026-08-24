<?php

declare(strict_types=1);

namespace LaraCeemes\Exceptions;

use RuntimeException;

final class StructureInUse extends RuntimeException
{
    public static function collection(string $handle): self
    {
        return new self("Collection [{$handle}] cannot be deleted while it contains Blueprints or Entries.");
    }

    public static function blueprint(string $handle): self
    {
        return new self("Blueprint [{$handle}] cannot be deleted while it contains Entries.");
    }
}
