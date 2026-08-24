<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

/**
 * @property string $uuid
 * @property string $group
 * @property string $key
 * @property mixed $value
 * @property string $type
 * @property bool $autoload
 */
final class Setting extends CeemesModel
{
    protected $table = 'ceemes_settings';

    protected $fillable = ['group', 'key', 'value', 'type', 'autoload'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
            'autoload' => 'boolean',
        ];
    }
}
