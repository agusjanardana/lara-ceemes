<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Settings;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Events\SettingUpdated;
use LaraCeemes\Models\Setting;
use LaraCeemes\Support\CeemesCache;

final class SetSetting extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    public function execute(
        string $key,
        mixed $value,
        ?string $type = null,
        bool $autoload = false,
    ): Setting {
        [$group, $settingKey] = $this->splitKey($key);
        $type ??= $this->inferType($value);

        $validated = Validator::make([
            'group' => $group,
            'key' => $settingKey,
            'type' => $type,
            'autoload' => $autoload,
        ], [
            'group' => ['required', 'alpha_dash:ascii', 'max:255'],
            'key' => ['required', 'alpha_dash:ascii', 'max:255'],
            'type' => ['required', Rule::in(['string', 'integer', 'float', 'boolean', 'array', 'null'])],
            'autoload' => ['boolean'],
        ])->validate();

        return $this->transaction(function () use ($validated, $value): Setting {
            $setting = Setting::query()->updateOrCreate(
                ['group' => $validated['group'], 'key' => $validated['key']],
                [
                    'value' => $this->normalize($value, $validated['type']),
                    'type' => $validated['type'],
                    'autoload' => $validated['autoload'],
                ],
            );

            $this->cache->forget('settings');
            event(new SettingUpdated($setting));

            return $setting;
        });
    }

    /** @return array{string, string} */
    private function splitKey(string $key): array
    {
        $parts = explode('.', $key, 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new InvalidArgumentException('Setting keys must use the group.key format.');
        }

        return [$parts[0], $parts[1]];
    }

    private function inferType(mixed $value): string
    {
        return match (true) {
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_bool($value) => 'boolean',
            is_array($value) => 'array',
            $value === null => 'null',
            default => 'string',
        };
    }

    private function normalize(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL),
            'array' => is_array($value) ? $value : [],
            'null' => null,
            default => (string) $value,
        };
    }
}
