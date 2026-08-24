<?php

declare(strict_types=1);

namespace LaraCeemes\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use InvalidArgumentException;
use LaraCeemes\Enums\EntryStatus;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\Entry;

final class EntryQuery
{
    /** @var Builder<Entry> */
    private Builder $builder;

    public function __construct(private readonly Collection $collection)
    {
        $this->builder = Entry::query()->where('collection_uuid', $collection->uuid);
    }

    public function published(): self
    {
        $this->builder->where('status', EntryStatus::Published->value);

        return $this;
    }

    public function draft(): self
    {
        $this->builder->where('status', EntryStatus::Draft->value);

        return $this;
    }

    public function whereSlug(string $slug): self
    {
        $this->builder->where('slug', $slug);

        return $this;
    }

    public function where(string $field, mixed $operator = null, mixed $value = null): self
    {
        $this->guardField($field);

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $column = $this->isNativeColumn($field) ? $field : "data->{$field}";
        $this->builder->where($column, $operator, $value);

        return $this;
    }

    public function orderBy(string $field, string $direction = 'asc'): self
    {
        $this->guardField($field);
        $direction = strtolower($direction);

        if (! in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Entry order direction must be asc or desc.');
        }

        $column = $this->isNativeColumn($field) ? $field : "data->{$field}";
        $this->builder->orderBy($column, $direction);

        return $this;
    }

    public function latest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'desc');
    }

    public function first(): ?Entry
    {
        return $this->builder->first();
    }

    public function firstOrFail(): Entry
    {
        return $this->builder->firstOrFail();
    }

    /** @return EloquentCollection<int, Entry> */
    public function get(): EloquentCollection
    {
        return $this->builder->get();
    }

    /** @return LengthAwarePaginator<int, Entry> */
    public function paginate(int $perPage = 15, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return $this->builder->paginate($perPage, ['*'], $pageName, $page);
    }

    public function collection(): Collection
    {
        return $this->collection;
    }

    private function guardField(string $field): void
    {
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $field) !== 1) {
            throw new InvalidArgumentException("Invalid Entry field [{$field}].");
        }
    }

    private function isNativeColumn(string $field): bool
    {
        return in_array($field, [
            'uuid',
            'title',
            'slug',
            'status',
            'created_at',
            'updated_at',
        ], true);
    }
}
