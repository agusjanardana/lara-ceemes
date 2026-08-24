<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Blueprints;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Models\Collection;
use LaraCeemes\Support\ContentCacheInvalidator;

final class CreateBlueprint extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(Collection $collection, array $data): Blueprint
    {
        if (trim((string) ($data['handle'] ?? '')) === '') {
            $data['handle'] = Str::slug((string) ($data['name'] ?? ''));
        }

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_blueprints', 'handle')->where('collection_uuid', $collection->uuid),
            ],
        ])->validate();

        return $this->transaction(function () use ($collection, $validated): Blueprint {
            $blueprint = $collection->blueprints()->create($validated);
            $this->cache->collection($collection->handle);

            return $blueprint;
        });
    }
}
