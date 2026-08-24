<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use LaraCeemes\Managers\SettingManager;

final class GetSettingCommand extends CeemesCommand
{
    protected $signature = 'ceemes:setting:get {key}';

    protected $description = 'Read a Lara Ceemes setting';

    public function handle(SettingManager $settings): int
    {
        $value = $settings->get($this->stringArgument('key'));
        $this->line(json_encode($value, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
