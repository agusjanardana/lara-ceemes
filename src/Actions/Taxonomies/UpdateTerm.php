<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Taxonomies;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Term;
use LaraCeemes\Support\CeemesCache;

final class UpdateTerm extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(Term $term, array $data): Term
    {
        $validated = Validator::make($data, [
            'parent_uuid' => [
                'nullable',
                Rule::exists('ceemes_terms', 'uuid')->where('taxonomy_uuid', $term->taxonomy_uuid),
                Rule::notIn([$term->uuid]),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes', 'required', 'alpha_dash:ascii', 'max:255',
                Rule::unique('ceemes_terms', 'slug')
                    ->where('taxonomy_uuid', $term->taxonomy_uuid)
                    ->ignore($term->uuid, 'uuid'),
            ],
            'data' => ['nullable', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($term, $validated): Term {
            $term->update($validated);
            $this->cache->forget("taxonomy:{$term->taxonomy->handle}");

            return $term->refresh();
        });
    }
}
