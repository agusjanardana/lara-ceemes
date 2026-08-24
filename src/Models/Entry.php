<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use LaraCeemes\Enums\EntryStatus;
use LaraCeemes\Fields\FieldRegistry;

/**
 * @property string $uuid
 * @property string $collection_uuid
 * @property string $blueprint_uuid
 * @property string $title
 * @property string $slug
 * @property string|null $uri
 * @property array<string, mixed> $data
 * @property array<string, mixed>|null $seo
 * @property EntryStatus $status
 * @property int|string|null $created_by
 * @property int|string|null $updated_by
 * @property-read Collection $collection
 * @property-read Blueprint $blueprint
 */
final class Entry extends CeemesModel
{
    use SoftDeletes;

    protected $table = 'ceemes_entries';

    protected $fillable = [
        'collection_uuid',
        'blueprint_uuid',
        'title',
        'slug',
        'uri',
        'data',
        'seo',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'seo' => 'array',
            'status' => EntryStatus::class,
        ];
    }

    /** @return BelongsTo<Collection, $this> */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class, 'collection_uuid', 'uuid');
    }

    /** @return BelongsTo<Blueprint, $this> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class, 'blueprint_uuid', 'uuid');
    }

    /** @return HasMany<Section, $this> */
    public function sectionItems(): HasMany
    {
        return $this->hasMany(Section::class, 'entry_uuid', 'uuid');
    }

    /** @return EloquentCollection<int, Section> */
    public function sections(bool $includeDisabled = false, string $fieldHandle = 'sections'): EloquentCollection
    {
        return $this->sectionItems()
            ->where('field_handle', $fieldHandle)
            ->when(! $includeDisabled, fn ($query) => $query->where('is_enabled', true))
            ->orderBy('sort_order')
            ->get();
    }

    public function section(string $identifier, string $fieldHandle = 'sections'): ?Section
    {
        $query = $this->sectionItems()
            ->where('field_handle', $fieldHandle)
            ->where('is_enabled', true);

        $keyed = (clone $query)->where('key', $identifier)->first();

        if ($keyed !== null) {
            return $keyed;
        }

        return $query
            ->whereHas('sectionType', fn ($typeQuery) => $typeQuery->where('handle', $identifier))
            ->orderBy('sort_order')
            ->first();
    }

    /** @return EloquentCollection<int, Section> */
    public function sectionsOfType(string $handle, string $fieldHandle = 'sections'): EloquentCollection
    {
        return $this->sectionItems()
            ->where('field_handle', $fieldHandle)
            ->where('is_enabled', true)
            ->whereHas('sectionType', fn ($query) => $query->where('handle', $handle))
            ->orderBy('sort_order')
            ->get();
    }

    /** @return BelongsToMany<Term, $this> */
    public function termRecords(): BelongsToMany
    {
        return $this->belongsToMany(
            Term::class,
            'ceemes_entry_term',
            'entry_uuid',
            'term_uuid',
            'uuid',
            'uuid',
        )->withPivot('field_handle');
    }

    /** @return EloquentCollection<int, Term> */
    public function terms(string $fieldHandle): EloquentCollection
    {
        return $this->termRecords()
            ->wherePivot('field_handle', $fieldHandle)
            ->orderBy('sort_order')
            ->get();
    }

    public function media(string $fieldHandle): mixed
    {
        $field = $this->blueprint->fields()
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

    /** @return array<string, mixed> */
    public function seo(): array
    {
        $seo = $this->getAttribute('seo');

        return is_array($seo) ? $seo : [];
    }

    public function publicUrl(): ?string
    {
        return is_string($this->uri) && $this->uri !== '' ? url($this->uri) : null;
    }
}
