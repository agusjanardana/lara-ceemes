<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Models\Site;
use LaraCeemes\Support\SiteContext;

final class SwitchSiteController
{
    public function __invoke(Request $request, SiteContext $sites): RedirectResponse
    {
        $site = Site::query()->whereKey($request->string('site_uuid')->toString())->where('is_enabled', true)->firstOrFail();
        $sites->use($site, persist: true);

        return back()->with('success', "Site aktif diubah ke {$site->name}.");
    }
}
