<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use LaraCeemes\Enums\ContentStatus;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\Concerns\BelongsToSite;

/**
 * @property string $uuid
 * @property string $set_uuid
 * @property string $site_uuid
 * @property string $title
 * @property string $slug
 * @property string|null $uri
 * @property array<string, mixed> $data
 * @property array<string, mixed>|null $seo
 * @property ContentStatus $status
 * @property int|string|null $created_by
 * @property int|string|null $updated_by
 * @property-read Set $set
 */
class Content extends CeemesModel
{
    use BelongsToSite;
    use SoftDeletes;

    protected $table = 'ceemes_contents';

    protected $fillable = [
        'set_uuid',
        'site_uuid',
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
            'status' => ContentStatus::class,
        ];
    }

    /** @return BelongsTo<Set, $this> */
    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class, 'set_uuid', 'uuid');
    }

    /** @return BelongsToMany<Section, $this> */
    public function placedSections(): BelongsToMany
    {
        return $this->belongsToMany(
            Section::class,
            'ceemes_content_section',
            'content_uuid',
            'section_uuid',
            'uuid',
            'uuid',
        )->withPivot(['uuid', 'region', 'key', 'sort_order', 'is_enabled'])->withTimestamps();
    }

    /** @return EloquentCollection<int, Section> */
    public function sections(bool $includeDisabled = false, string $fieldHandle = 'sections'): EloquentCollection
    {
        $query = $this->placedSections()->wherePivot('region', $fieldHandle);

        if (! $includeDisabled) {
            $query->wherePivot('is_enabled', true);
        }

        return $query->orderByPivot('sort_order')->get();
    }

    public function section(string $identifier, string $fieldHandle = 'sections'): ?Section
    {
        $query = $this->placedSections()
            ->wherePivot('region', $fieldHandle)
            ->wherePivot('is_enabled', true);

        $keyed = (clone $query)->wherePivot('key', $identifier)->first();

        if ($keyed !== null) {
            return $keyed;
        }

        return $query
            ->whereHas('sectionType', fn ($typeQuery) => $typeQuery->where('handle', $identifier))
            ->orderByPivot('sort_order')
            ->first();
    }

    /** @return EloquentCollection<int, Section> */
    public function sectionsOfType(string $handle, string $fieldHandle = 'sections'): EloquentCollection
    {
        return $this->placedSections()
            ->wherePivot('region', $fieldHandle)
            ->wherePivot('is_enabled', true)
            ->whereHas('sectionType', fn ($query) => $query->where('handle', $handle))
            ->orderByPivot('sort_order')
            ->get();
    }

    /** @return BelongsToMany<Category, $this> */
    public function categoryRecords(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            'ceemes_content_category',
            'content_uuid',
            'category_uuid',
            'uuid',
            'uuid',
        )->withPivot('field_handle');
    }

    /** @return EloquentCollection<int, Category> */
    public function categories(string $fieldHandle): EloquentCollection
    {
        return $this->categoryRecords()
            ->wherePivot('field_handle', $fieldHandle)
            ->orderBy('sort_order')
            ->get();
    }

    public function media(string $fieldHandle): mixed
    {
        $field = $this->set->fields()
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
        if (! is_string($this->uri) || $this->uri === '') {
            return null;
        }

        $prefix = '';
        if ((bool) config('ceemes.multisite.enabled', false)) {
            $site = $this->site;
            if ($site === null) {
                return null;
            }
            $prefix = $site->pathPrefix();
        }

        $path = $prefix.($this->uri === '/' ? '' : $this->uri);

        return url($path === '' ? '/' : $path);
    }
}
