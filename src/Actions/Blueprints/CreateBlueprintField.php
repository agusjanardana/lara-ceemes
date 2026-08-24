<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Blueprints;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Models\BlueprintField;
use LaraCeemes\Support\ContentCacheInvalidator;

final class CreateBlueprintField extends Action
{
    public function __construct(
        private readonly FieldRegistry $fields,
        private readonly ContentCacheInvalidator $cache,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(Blueprint $blueprint, array $data): BlueprintField
    {
        if (trim((string) ($data['handle'] ?? '')) === '') {
            $data['handle'] = Str::slug((string) ($data['label'] ?? ''), '_');
        }

        $validated = Validator::make($data, [
            'handle' => [
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_blueprint_fields', 'handle')->where('blueprint_uuid', $blueprint->uuid),
            ],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys($this->fields->all()))],
            'config' => ['sometimes', 'array'],
            'width' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($blueprint, $validated): BlueprintField {
            $field = $blueprint->fields()->create($validated);
            $this->cache->collection($blueprint->collection->handle);

            return $field;
        });
    }
}
