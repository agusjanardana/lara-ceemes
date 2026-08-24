<?php

declare(strict_types=1);

namespace LaraCeemes\Exceptions;

use LogicException;

final class FieldTypeAlreadyRegistered extends LogicException
{
    public static function forHandle(string $handle): self
    {
        return new self("Field type [{$handle}] is already registered.");
    }
}
