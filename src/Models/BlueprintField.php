<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property string $blueprint_uuid
 * @property string $handle
 * @property string $label
 * @property string $type
 * @property array<string, mixed>|null $config
 * @property int $width
 * @property int $sort_order
 * @property-read Blueprint $blueprint
 */
final class BlueprintField extends CeemesModel
{
    protected $table = 'ceemes_blueprint_fields';

    protected $fillable = [
        'blueprint_uuid',
        'handle',
        'label',
        'type',
        'config',
        'width',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'width' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /** @return BelongsTo<Blueprint, $this> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class, 'blueprint_uuid', 'uuid');
    }
}
