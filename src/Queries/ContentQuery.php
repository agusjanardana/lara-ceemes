<?php

declare(strict_types=1);

namespace LaraCeemes\Queries;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use InvalidArgumentException;
use LaraCeemes\Enums\ContentStatus;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Set;

final class ContentQuery
{
    /** @var Builder<Content> */
    private Builder $builder;

    public function __construct(private readonly Set $set)
    {
        $this->builder = Content::query()->where('set_uuid', $set->uuid);
    }

    public function published(): self
    {
        $this->builder->where('status', ContentStatus::Published->value);

        return $this;
    }

    public function draft(): self
    {
        $this->builder->where('status', ContentStatus::Draft->value);

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
            throw new InvalidArgumentException('Content order direction must be asc or desc.');
        }

        $column = $this->isNativeColumn($field) ? $field : "data->{$field}";
        $this->builder->orderBy($column, $direction);

        return $this;
    }

    public function latest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'desc');
    }

    public function first(): ?Content
    {
        return $this->builder->first();
    }

    public function firstOrFail(): Content
    {
        return $this->builder->firstOrFail();
    }

    /** @return EloquentCollection<int, Content> */
    public function get(): EloquentCollection
    {
        return $this->builder->get();
    }

    /** @return LengthAwarePaginator<int, Content> */
    public function paginate(int $perPage = 15, string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return $this->builder->paginate($perPage, ['*'], $pageName, $page);
    }

    public function set(): Set
    {
        return $this->set;
    }

    private function guardField(string $field): void
    {
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $field) !== 1) {
            throw new InvalidArgumentException("Invalid Content field [{$field}].");
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
