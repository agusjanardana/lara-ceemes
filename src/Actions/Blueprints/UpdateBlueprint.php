<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Blueprints;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Support\ContentCacheInvalidator;

final class UpdateBlueprint extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(Blueprint $blueprint, array $data): Blueprint
    {
        $validated = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'handle' => [
                'sometimes',
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_blueprints', 'handle')
                    ->where('collection_uuid', $blueprint->collection_uuid)
                    ->ignore($blueprint->uuid, 'uuid'),
            ],
        ])->validate();

        return $this->transaction(function () use ($blueprint, $validated): Blueprint {
            $blueprint->update($validated);
            $this->cache->collection($blueprint->collection->handle);

            return $blueprint->refresh();
        });
    }
}
