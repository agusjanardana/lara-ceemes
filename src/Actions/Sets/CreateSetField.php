<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sets;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\Set;
use LaraCeemes\Models\SetField;
use LaraCeemes\Support\ContentCacheInvalidator;

final class CreateSetField extends Action
{
    public function __construct(private readonly FieldRegistry $fields, private readonly ContentCacheInvalidator $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(Set $set, array $data): SetField
    {
        if (trim((string) ($data['handle'] ?? '')) === '') {
            $data['handle'] = Str::slug((string) ($data['label'] ?? ''), '_');
        }

        $validated = Validator::make($data, [
            'handle' => ['required', 'alpha_dash:ascii', 'max:255', Rule::unique('ceemes_set_fields', 'handle')->where('set_uuid', $set->uuid)],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys($this->fields->all()))],
            'config' => ['sometimes', 'array'],
            'width' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        return $this->transaction(function () use ($set, $validated): SetField {
            $field = $set->fields()->create($validated);
            $this->cache->set($set->handle);

            return $field;
        });
    }
}
