<?php

declare(strict_types=1);

namespace LaraCeemes\Fields\Types;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Models\Set;
use LaraCeemes\Support\SiteContext;

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
        $exists = Rule::exists('ceemes_contents', 'uuid')
            ->where('site_uuid', app(SiteContext::class)->current()->uuid);

        if (is_string($setUuid)) {
            $exists->where('set_uuid', $setUuid);
        }

        if (! $this->allowsMultiple($config)) {
            return [$this->presenceRule($config), 'uuid', $exists];
        }

        $rules = [
            $this->presenceRule($config),
            'array',
            function (string $attribute, mixed $value, callable $fail) use ($exists): void {
                if (is_array($value) && Validator::make($value, ['*' => ['uuid', $exists]])->fails()) {
                    $fail("Pilihan {$attribute} memuat Content yang tidak valid.");
                }
            },
        ];

        if (isset($config['min_items'])) {
            $rules[] = 'min:'.(int) $config['min_items'];
        }

        if (isset($config['max_items'])) {
            $rules[] = 'max:'.(int) $config['max_items'];
        }

        return $rules;
    }
}
