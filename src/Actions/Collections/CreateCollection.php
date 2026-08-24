<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Collections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\CollectionCreated;
use LaraCeemes\Models\Collection;
use LaraCeemes\Support\ContentCacheInvalidator;

final class CreateCollection extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data): Collection
    {
        $data['handle'] ??= Str::slug((string) ($data['name'] ?? ''));

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'alpha_dash:ascii', 'max:255', Rule::unique('ceemes_collections', 'handle')],
            'description' => ['nullable', 'string'],
            'route' => ['nullable', 'string', 'max:255'],
            'template' => ['nullable', 'string', 'max:255'],
            'is_publishable' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($validated): Collection {
            $collection = Collection::query()->create($validated);

            $this->cache->collection($collection->handle);
            event(new CollectionCreated($collection));

            return $collection;
        });
    }
}
