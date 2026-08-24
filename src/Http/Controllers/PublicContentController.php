<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use LaraCeemes\Enums\EntryStatus;
use LaraCeemes\Managers\SeoManager;
use LaraCeemes\Models\Entry;

final class PublicContentController
{
    public function __invoke(Factory $views, SeoManager $seoManager, ?string $ceemesPath = null): View
    {
        $uri = $ceemesPath === null || $ceemesPath === '' ? '/' : '/'.trim($ceemesPath, '/');

        if (! Schema::hasTable('ceemes_entries') || ! Schema::hasColumn('ceemes_entries', 'uri')) {
            if ($uri === '/' && (bool) config('ceemes.homepage.enabled', true)) {
                return $views->make('ceemes::home');
            }

            abort(404);
        }

        $entry = Entry::query()
            ->with(['collection', 'blueprint.fields'])
            ->where('uri', $uri)
            ->where('status', EntryStatus::Published->value)
            ->whereHas('collection', fn ($query) => $query->where('is_publishable', true))
            ->first();

        if ($entry === null) {
            if ($uri === '/' && (bool) config('ceemes.homepage.enabled', true)) {
                return $views->make('ceemes::home');
            }

            abort(404);
        }

        $template = is_string($entry->collection->template) ? trim($entry->collection->template) : '';
        $view = $template !== '' && $views->exists($template) ? $template : 'ceemes::content.entry';

        return $views->make($view, [
            'entry' => $entry,
            'seo' => $seoManager->forEntry($entry),
        ]);
    }
}
