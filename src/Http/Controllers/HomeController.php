<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

final class HomeController
{
    public function __invoke(Factory $views): View
    {
        return $views->make('ceemes::home');
    }
}
