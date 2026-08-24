<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Taxonomies;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Taxonomy;
use LaraCeemes\Models\Term;
use LaraCeemes\Support\CeemesCache;

final class CreateTerm extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(Taxonomy $taxonomy, array $data): Term
    {
        $data['slug'] ??= Str::slug((string) ($data['name'] ?? ''));
        $validated = Validator::make($data, [
            'parent_uuid' => [
                'nullable',
                Rule::exists('ceemes_terms', 'uuid')->where('taxonomy_uuid', $taxonomy->uuid),
            ],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_terms', 'slug')->where('taxonomy_uuid', $taxonomy->uuid),
            ],
            'data' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($taxonomy, $validated): Term {
            $term = $taxonomy->terms()->create($validated);
            $this->cache->forget("taxonomy:{$taxonomy->handle}");

            return $term;
        });
    }
}
