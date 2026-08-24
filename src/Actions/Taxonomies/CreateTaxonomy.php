<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Taxonomies;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Taxonomy;
use LaraCeemes\Support\CeemesCache;

final class CreateTaxonomy extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data): Taxonomy
    {
        $data['handle'] ??= Str::slug((string) ($data['name'] ?? ''));
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'alpha_dash:ascii', 'max:255', Rule::unique('ceemes_taxonomies', 'handle')],
            'description' => ['nullable', 'string'],
        ])->validate();

        return $this->transaction(function () use ($validated): Taxonomy {
            $taxonomy = Taxonomy::query()->create($validated);
            $this->cache->forget("taxonomy:{$taxonomy->handle}");

            return $taxonomy;
        });
    }
}
