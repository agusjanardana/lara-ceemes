<?php

declare(strict_types=1);

namespace LaraCeemes\Exceptions;

use InvalidArgumentException;

final class UnknownFieldType extends InvalidArgumentException
{
    public static function forHandle(string $handle): self
    {
        return new self("Field type [{$handle}] is not registered.");
    }
}
