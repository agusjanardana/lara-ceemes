<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property string $set_uuid
 * @property string $handle
 * @property string $label
 * @property string $type
 * @property array<string, mixed>|null $config
 * @property int $width
 * @property int $sort_order
 * @property-read Set $set
 */
final class SetField extends CeemesModel
{
    protected $table = 'ceemes_set_fields';

    protected $fillable = ['set_uuid', 'handle', 'label', 'type', 'config', 'width', 'sort_order'];

    protected function casts(): array
    {
        return ['config' => 'array', 'width' => 'integer', 'sort_order' => 'integer'];
    }

    /** @return BelongsTo<Set, $this> */
    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class, 'set_uuid', 'uuid');
    }
}
