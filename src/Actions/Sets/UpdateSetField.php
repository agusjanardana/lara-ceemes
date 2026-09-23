<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sets;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\SetField;
use LaraCeemes\Support\ContentCacheInvalidator;

final class UpdateSetField extends Action
{
    public function __construct(private readonly FieldRegistry $fields, private readonly ContentCacheInvalidator $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(SetField $field, array $data): SetField
    {
        $validated = Validator::make($data, [
            'handle' => ['sometimes', 'required', 'alpha_dash:ascii', 'max:255', Rule::unique('ceemes_set_fields', 'handle')->where('set_uuid', $field->set_uuid)->ignore($field->uuid, 'uuid')],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', Rule::in(array_keys($this->fields->all()))],
            'config' => ['sometimes', 'array'],
            'width' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($field, $validated): SetField {
            $previousHandle = $field->handle;
            $field->update($validated);

            if ($field->handle !== $previousHandle) {
                $field->set->fields()
                    ->where('uuid', '!=', $field->uuid)
                    ->get()
                    ->each(function (SetField $dependent) use ($previousHandle, $field): void {
                        $config = is_array($dependent->config) ? $dependent->config : [];
                        $visibility = is_array($config['visibility'] ?? null) ? $config['visibility'] : [];
                        if (($visibility['field'] ?? null) !== $previousHandle) {
                            return;
                        }

                        $visibility['field'] = $field->handle;
                        $config['visibility'] = $visibility;
                        $dependent->update(['config' => $config]);
                    });
            }

            $this->cache->set($field->set->handle);

            return $field->refresh();
        });
    }
}
