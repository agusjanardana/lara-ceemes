<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Models\Set;

final class ContentField extends AbstractRelationField
{
    public function handle(): string
    {
        return 'content';
    }

    public function rules(array $config = []): array
    {
        $setUuid = is_string($config['set'] ?? null)
            ? Set::query()->where('handle', $config['set'])->value('uuid')
            : null;
        $exists = Rule::exists('ceemes_contents', 'uuid');

        if (is_string($setUuid)) {
            $exists->where('set_uuid', $setUuid);
        }

        if (! $this->allowsMultiple($config)) {
            return [$this->presenceRule($config), 'uuid', $exists];
        }

        return [
            $this->presenceRule($config),
            'array',
            function (string $attribute, mixed $value, callable $fail) use ($exists): void {
                if (is_array($value) && Validator::make($value, ['*' => ['uuid', $exists]])->fails()) {
                    $fail("The {$attribute} selection contains invalid Content.");
                }
            },
        ];
    }
}
