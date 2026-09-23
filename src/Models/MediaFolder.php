<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $uuid
 * @property string|null $parent_uuid
 * @property string $disk
 * @property string $name
 * @property string $path
 * @property int|string|null $created_by
 */
final class MediaFolder extends CeemesModel
{
    protected $table = 'ceemes_media_folders';

    protected $fillable = ['parent_uuid', 'disk', 'name', 'path', 'created_by'];

    /** @return BelongsTo<MediaFolder, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_uuid');
    }

    /** @return HasMany<MediaFolder, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_uuid')->orderBy('name');
    }

    /** @return HasMany<Media, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class, 'folder_uuid');
    }
}
