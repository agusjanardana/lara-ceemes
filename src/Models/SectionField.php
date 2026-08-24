<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $uuid
 * @property string $section_type_uuid
 * @property string $handle
 * @property string $label
 * @property string $type
 * @property array<string, mixed>|null $config
 * @property int $width
 * @property int $sort_order
 * @property-read SectionType $sectionType
 */
final class SectionField extends CeemesModel
{
    protected $table = 'ceemes_section_fields';

    protected $fillable = [
        'section_type_uuid',
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

    /** @return BelongsTo<SectionType, $this> */
    public function sectionType(): BelongsTo
    {
        return $this->belongsTo(SectionType::class, 'section_type_uuid', 'uuid');
    }
}
