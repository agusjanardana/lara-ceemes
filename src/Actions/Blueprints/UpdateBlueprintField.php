<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Blueprints;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\BlueprintField;
use LaraCeemes\Support\ContentCacheInvalidator;

final class UpdateBlueprintField extends Action
{
    public function __construct(
        private readonly FieldRegistry $fields,
        private readonly ContentCacheInvalidator $cache,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(BlueprintField $field, array $data): BlueprintField
    {
        $validated = Validator::make($data, [
            'handle' => [
                'sometimes',
                'required',
                'alpha_dash:ascii',
                'max:255',
                Rule::unique('ceemes_blueprint_fields', 'handle')
                    ->where('blueprint_uuid', $field->blueprint_uuid)
                    ->ignore($field->uuid, 'uuid'),
            ],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::in(array_keys($this->fields->all()))],
            'config' => ['sometimes', 'array'],
            'width' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($field, $validated): BlueprintField {
            $field->update($validated);
            $this->cache->collection($field->blueprint->collection->handle);

            return $field->refresh();
        });
    }
}
