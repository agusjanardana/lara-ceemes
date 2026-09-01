<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Arr;
use LaraCeemes\Fields\FieldRegistry;

/**
 * @property string $uuid
 * @property string $section_type_uuid
 * @property string|null $name
 * @property string|null $handle
 * @property array<string, mixed> $data
 * @property-read SectionType $sectionType
 */
final class Section extends CeemesModel
{
    protected $table = 'ceemes_sections';

    protected $fillable = [
        'section_type_uuid',
        'name',
        'handle',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    /** @return BelongsTo<SectionType, $this> */
    public function sectionType(): BelongsTo
    {
        return $this->belongsTo(SectionType::class, 'section_type_uuid', 'uuid');
    }

    /** @return BelongsToMany<Content, $this> */
    public function contents(): BelongsToMany
    {
        return $this->belongsToMany(
            Content::class,
            'ceemes_content_section',
            'section_uuid',
            'content_uuid',
            'uuid',
            'uuid',
        )->withPivot(['uuid', 'region', 'key', 'sort_order', 'is_enabled'])->withTimestamps();
    }

    public function get(string $handle, mixed $default = null): mixed
    {
        return data_get($this->data(), $handle, $default);
    }

    public function has(string $handle): bool
    {
        return Arr::has($this->data(), $handle);
    }

    /** @return array<string, mixed> */
    public function data(): array
    {
        $data = $this->getAttribute('data');

        return is_array($data) ? $data : [];
    }

    public function type(): SectionType
    {
        return $this->sectionType;
    }

    public function handle(): string
    {
        return $this->sectionType->handle;
    }

    public function media(string $fieldHandle): mixed
    {
        $field = $this->sectionType->fields()
            ->where('handle', $fieldHandle)
            ->where('type', 'media')
            ->first();

        if ($field === null) {
            return null;
        }

        return app(FieldRegistry::class)
            ->get('media')
            ->resolve($this->get($fieldHandle), is_array($field->config) ? $field->config : []);
    }
}
