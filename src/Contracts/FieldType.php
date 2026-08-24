<?php

declare(strict_types=1);

namespace LaraCeemes\Contracts;

interface FieldType
{
    public function handle(): string;

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, mixed>
     */
    public function rules(array $config = []): array;

    /** @param array<string, mixed> $config */
    public function normalize(mixed $value, array $config = []): mixed;

    /** @param array<string, mixed> $config */
    public function serialize(mixed $value, array $config = []): mixed;

    /** @param array<string, mixed> $config */
    public function resolve(mixed $value, array $config = []): mixed;

    public function adminView(): string;
}
