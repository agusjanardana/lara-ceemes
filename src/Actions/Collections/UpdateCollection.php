<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Collections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\CollectionUpdated;
use LaraCeemes\Models\Collection;
use LaraCeemes\Support\ContentCacheInvalidator;

final class UpdateCollection extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(Collection $collection, array $data): Collection
    {
        $validated = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'handle' => [
                'sometimes',
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_collections', 'handle')->ignore($collection->uuid, 'uuid'),
            ],
            'description' => ['nullable', 'string'],
            'route' => ['nullable', 'string', 'max:255'],
            'template' => ['nullable', 'string', 'max:255'],
            'is_publishable' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($collection, $validated): Collection {
            $oldHandle = $collection->handle;
            $collection->update($validated);

            $this->cache->collection($oldHandle);
            $this->cache->collection($collection->handle);
            event(new CollectionUpdated($collection));

            return $collection->refresh();
        });
    }
}
