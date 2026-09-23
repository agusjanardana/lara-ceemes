<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

final class FieldVisibility
{
    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $values
     */
    public function allows(array $config, array $values): bool
    {
        $visibility = is_array($config['visibility'] ?? null) ? $config['visibility'] : [];

        if (($visibility['enabled'] ?? false) !== true) {
            return true;
        }

        $field = is_string($visibility['field'] ?? null) ? $visibility['field'] : '';
        $operator = is_string($visibility['operator'] ?? null) ? $visibility['operator'] : 'filled';
        $actual = $field !== '' ? ($values[$field] ?? null) : null;
        $expected = $visibility['value'] ?? null;

        return match ($operator) {
            'empty' => $this->isEmpty($actual),
            'equals' => $this->equals($actual, $expected),
            'not_equals' => ! $this->equals($actual, $expected),
            'contains' => $this->contains($actual, $expected),
            'not_contains' => ! $this->contains($actual, $expected),
            'truthy' => filter_var($actual, FILTER_VALIDATE_BOOLEAN),
            'falsy' => ! filter_var($actual, FILTER_VALIDATE_BOOLEAN),
            default => ! $this->isEmpty($actual),
        };
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private function equals(mixed $actual, mixed $expected): bool
    {
        if (is_array($actual)) {
            return count($actual) === 1 && (string) reset($actual) === (string) $expected;
        }

        return (string) $actual === (string) $expected;
    }

    private function contains(mixed $actual, mixed $expected): bool
    {
        if (is_array($actual)) {
            return in_array((string) $expected, array_map('strval', $actual), true);
        }

        return str_contains((string) $actual, (string) $expected);
    }
}
