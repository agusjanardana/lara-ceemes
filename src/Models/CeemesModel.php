<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

abstract class CeemesModel extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'uuid';

    /**
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }
}
