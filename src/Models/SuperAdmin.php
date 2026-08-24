<?php

declare(strict_types=1);

namespace LaraCeemes\Models;

final class SuperAdmin extends CeemesModel
{
    protected $table = 'ceemes_super_admins';

    protected $fillable = [
        'user_key',
    ];
}
