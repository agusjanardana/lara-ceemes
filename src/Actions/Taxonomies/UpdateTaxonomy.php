<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Taxonomies;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Taxonomy;
use LaraCeemes\Support\CeemesCache;

final class UpdateTaxonomy extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(Taxonomy $taxonomy, array $data): Taxonomy
    {
        $validated = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'handle' => [
                'sometimes', 'required', 'alpha_dash:ascii', 'max:255',
                Rule::unique('ceemes_taxonomies', 'handle')->ignore($taxonomy->uuid, 'uuid'),
            ],
            'description' => ['nullable', 'string'],
        ])->validate();

        return $this->transaction(function () use ($taxonomy, $validated): Taxonomy {
            $oldHandle = $taxonomy->handle;
            $taxonomy->update($validated);
            $this->cache->forget("taxonomy:{$oldHandle}");
            $this->cache->forget("taxonomy:{$taxonomy->handle}");

            return $taxonomy->refresh();
        });
    }
}
