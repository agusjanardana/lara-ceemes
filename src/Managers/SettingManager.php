<?php

declare(strict_types=1);

namespace LaraCeemes\Managers;

use LaraCeemes\Actions\Settings\SetSetting;
use LaraCeemes\Models\Setting;
use LaraCeemes\Support\CeemesCache;

final class SettingManager extends Manager
{
    public function __construct(CeemesCache $cache, private readonly SetSetting $setSetting)
    {
        parent::__construct($cache);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values()[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values());
    }

    public function set(string $key, mixed $value, ?string $type = null, bool $autoload = false): Setting
    {
        return $this->setSetting->execute($key, $value, $type, $autoload);
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->values();
    }

    /** @return array<string, mixed> */
    private function values(): array
    {
        return $this->cache->remember('settings', function (): array {
            $values = [];

            foreach (Setting::query()->get() as $setting) {
                $values["{$setting->group}.{$setting->key}"] = $setting->value;
            }

            return $values;
        });
    }
}
