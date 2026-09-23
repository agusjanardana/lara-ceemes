<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use LaraCeemes\Enums\ContentStatus;
use LaraCeemes\Managers\SeoManager;
use LaraCeemes\Models\Content;
use LaraCeemes\Support\SiteContext;

final class PublicContentController
{
    public function __invoke(Factory $views, SeoManager $seoManager, SiteContext $sites, Request $request): View
    {
        $sites->current();
        $ceemesPath = $request->route('ceemesPath');
        $ceemesPath = is_string($ceemesPath) ? $ceemesPath : null;
        $uri = $ceemesPath === null || $ceemesPath === '' ? '/' : '/'.trim($ceemesPath, '/');

        if (! Schema::hasTable('ceemes_contents') || ! Schema::hasColumn('ceemes_contents', 'uri')) {
            if ($uri === '/' && (bool) config('ceemes.homepage.enabled', true)) {
                return $views->make('ceemes::home');
            }

            abort(404);
        }

        $content = Content::query()
            ->with(['set.fields'])
            ->where('uri', $uri)
            ->where('status', ContentStatus::Published->value)
            ->whereHas('set', fn ($query) => $query->where('is_publishable', true))
            ->first();

        if ($content === null) {
            if ($uri === '/' && (bool) config('ceemes.homepage.enabled', true)) {
                return $views->make('ceemes::home');
            }

            abort(404);
        }

        $template = is_string($content->set->template) ? trim($content->set->template) : '';
        $view = $template !== '' && $views->exists($template) ? $template : 'ceemes::content.show';

        return $views->make($view, [
            'content' => $content,
            'set' => $content->set,
            'fields' => $content->set->fields,
            'sections' => $content->placedSections()
                ->wherePivot('is_enabled', true)
                ->orderByPivot('sort_order')
                ->get(),
            'seo' => $seoManager->forContent($content),
        ]);
    }
}
