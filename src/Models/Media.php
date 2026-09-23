<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $uuid
 * @property string|null $folder_uuid
 * @property string $disk
 * @property string $directory
 * @property string $filename
 * @property string $original_filename
 * @property string $extension
 * @property string $mime_type
 * @property int $size
 * @property int|null $width
 * @property int|null $height
 * @property string|null $title
 * @property string|null $alt
 * @property string|null $caption
 * @property int|string|null $uploaded_by
 * @property-read MediaFolder|null $folder
 */
final class Media extends CeemesModel
{
    use SoftDeletes;

    protected $table = 'ceemes_media';

    protected $fillable = [
        'uuid',
        'folder_uuid',
        'disk',
        'directory',
        'filename',
        'original_filename',
        'extension',
        'mime_type',
        'size',
        'width',
        'height',
        'title',
        'alt',
        'caption',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function path(): string
    {
        return ltrim(trim($this->directory, '/').'/'.$this->filename, '/');
    }

    /** @return BelongsTo<MediaFolder, $this> */
    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_uuid');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path());
    }
}
