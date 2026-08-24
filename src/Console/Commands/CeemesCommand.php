<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Console\Command;
use LogicException;

abstract class CeemesCommand extends Command
{
    protected function stringArgument(string $key): string
    {
        $value = $this->argument($key);

        if (! is_string($value)) {
            throw new LogicException("The [{$key}] argument must be a string.");
        }

        return $value;
    }

    protected function stringOption(string $key): ?string
    {
        $value = $this->option($key);

        return is_string($value) ? $value : null;
    }

    protected function stringOptionOrAsk(string $key, string $question): string
    {
        return $this->stringOption($key) ?? (string) $this->ask($question);
    }
}
