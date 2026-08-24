<?php

declare(strict_types=1);

namespace LaraCeemes\Events;

use LaraCeemes\Models\Setting;

final readonly class SettingUpdated
{
    public function __construct(public Setting $setting) {}
}
