<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Fixtures;

use Illuminate\Foundation\Auth\User as Authenticatable;

final class User extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
