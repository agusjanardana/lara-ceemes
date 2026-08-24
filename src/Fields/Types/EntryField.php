<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Models\Collection;

final class EntryField extends AbstractRelationField
{
    public function handle(): string
    {
        return 'entry';
    }

    public function rules(array $config = []): array
    {
        $collectionUuid = null;

        if (is_string($config['collection'] ?? null)) {
            $collectionUuid = Collection::query()->where('handle', $config['collection'])->value('uuid');
        }

        $exists = Rule::exists('ceemes_entries', 'uuid');

        if (is_string($collectionUuid)) {
            $exists->where('collection_uuid', $collectionUuid);
        }

        if (! $this->allowsMultiple($config)) {
            return [$this->presenceRule($config), 'uuid', $exists];
        }

        return [
            $this->presenceRule($config),
            'array',
            function (string $attribute, mixed $value, callable $fail) use ($exists): void {
                if (! is_array($value)) {
                    return;
                }

                $validator = Validator::make($value, ['*' => ['uuid', $exists]]);

                if ($validator->fails()) {
                    $fail("The {$attribute} selection contains an invalid Entry.");
                }
            },
        ];
    }
}
