<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sites;

use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Site;
use LaraCeemes\Support\SiteContext;

final class DeleteSite extends Action
{
    public function __construct(private readonly SiteContext $sites) {}

    public function execute(Site $site): void
    {
        if ($site->is_default) {
            throw ValidationException::withMessages(['site' => 'Default Site tidak dapat dihapus.']);
        }
        if ($site->contents()->exists() || $site->sections()->exists() || $site->navigations()->exists()) {
            throw ValidationException::withMessages(['site' => 'Site masih memiliki Content, Section, atau Navigation dan belum dapat dihapus.']);
        }

        $site->delete();
        $this->sites->forget();
    }
}
