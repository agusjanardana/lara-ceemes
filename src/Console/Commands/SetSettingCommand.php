<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use LaraCeemes\Actions\Settings\SetSetting;

final class SetSettingCommand extends CeemesCommand
{
    protected $signature = 'ceemes:setting:set {key} {value} {--type=} {--autoload}';

    protected $description = 'Write a Lara Ceemes setting';

    public function handle(SetSetting $action): int
    {
        $type = $this->option('type');
        $value = $this->value($this->stringArgument('value'), is_string($type) ? $type : null);
        $setting = $action->execute(
            $this->stringArgument('key'),
            $value,
            is_string($type) ? $type : null,
            (bool) $this->option('autoload'),
        );
        $this->components->success("Setting [{$setting->group}.{$setting->key}] updated.");

        return self::SUCCESS;
    }

    private function value(string $value, ?string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL),
            'array' => json_decode($value, true),
            'null' => null,
            default => $value,
        };
    }
}
