<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string $name
 * @property string $handle
 * @property string $locale
 * @property bool $is_default
 * @property bool $is_enabled
 * @property int $sort_order
 */
final class Site extends CeemesModel
{
    protected $table = 'ceemes_sites';

    protected $fillable = ['name', 'handle', 'locale', 'is_default', 'is_enabled', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return HasMany<Content, $this> */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class, 'site_uuid', 'uuid')->withoutGlobalScope('ceemes_site');
    }

    /** @return HasMany<Section, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'site_uuid', 'uuid')->withoutGlobalScope('ceemes_site');
    }

    /** @return HasMany<Navigation, $this> */
    public function navigations(): HasMany
    {
        return $this->hasMany(Navigation::class, 'site_uuid', 'uuid')->withoutGlobalScope('ceemes_site');
    }

    public function pathPrefix(): string
    {
        return (bool) config('ceemes.multisite.enabled', false) ? '/'.$this->handle : '';
    }
}
