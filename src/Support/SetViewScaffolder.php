<?php

declare(strict_types=1);

namespace LaraCeemes\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LaraCeemes\Models\Set;
use RuntimeException;

final class SetViewScaffolder
{
    public function __construct(private readonly Filesystem $files) {}

    public function viewName(string $handle): string
    {
        return $handle === 'pages' ? 'pages' : "{$handle}.show";
    }

    public function routePattern(string $handle): string
    {
        return $handle === 'pages' ? '/{slug}' : '/'.Str::slug($handle).'/{slug}';
    }

    public function path(string $handle): string
    {
        $configuredRoot = config('ceemes.views.path');
        $viewRoot = is_string($configuredRoot) && $configuredRoot !== ''
            ? rtrim($configuredRoot, '\\/')
            : resource_path('views');

        return $handle === 'pages'
            ? $viewRoot.'/pages.blade.php'
            : $viewRoot."/{$handle}/show.blade.php";
    }

    public function isConvention(Set $set): bool
    {
        return $set->template === $this->viewName($set->handle);
    }

    public function scaffold(Set $set): bool
    {
        if (! (bool) config('ceemes.views.auto_scaffold', true) || ! $this->isConvention($set)) {
            return false;
        }

        $target = $this->path($set->handle);

        if ($this->files->exists($target)) {
            return false;
        }

        $stub = dirname(__DIR__, 2).'/resources/stubs/set-view.blade.php';

        if (! $this->files->exists($stub)) {
            throw new RuntimeException('Lara Ceemes Set view stub is missing.');
        }

        $this->files->ensureDirectoryExists(dirname($target));
        $this->files->copy($stub, $target);

        return true;
    }
}
