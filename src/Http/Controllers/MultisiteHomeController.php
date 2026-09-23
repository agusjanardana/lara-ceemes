<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use LaraCeemes\Support\SiteContext;

final class MultisiteHomeController
{
    public function __invoke(SiteContext $sites, Factory $views): RedirectResponse|View
    {
        if (! Schema::hasTable('ceemes_sites')) {
            return $views->make('ceemes::home');
        }

        return redirect()->to('/'.$sites->current()->handle);
    }
}
