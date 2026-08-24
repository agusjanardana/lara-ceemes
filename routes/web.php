<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaraCeemes\Http\Controllers\Admin\BlueprintController;
use LaraCeemes\Http\Controllers\Admin\CollectionController;
use LaraCeemes\Http\Controllers\Admin\DashboardController;
use LaraCeemes\Http\Controllers\Admin\EntryController;
use LaraCeemes\Http\Controllers\Admin\FieldController;
use LaraCeemes\Http\Controllers\Admin\MediaController;
use LaraCeemes\Http\Controllers\Admin\NavigationController;
use LaraCeemes\Http\Controllers\Admin\NavigationItemController;
use LaraCeemes\Http\Controllers\Admin\SectionController;
use LaraCeemes\Http\Controllers\Admin\SectionFieldController;
use LaraCeemes\Http\Controllers\Admin\SectionTypeController;
use LaraCeemes\Http\Controllers\Admin\SettingController;
use LaraCeemes\Http\Controllers\Admin\TaxonomyController;
use LaraCeemes\Http\Controllers\Admin\TermController;
use LaraCeemes\Http\Controllers\Auth\LoginController;

$adminPrefix = trim((string) config('ceemes.admin.prefix', 'admin'), '/');
$loginRouteName = (string) config('ceemes.auth.login_route_name', 'login');

if ((bool) config('ceemes.auth.routes', true) && ! Route::has($loginRouteName)) {
    Route::middleware('web')->prefix($adminPrefix)->group(function () use ($loginRouteName): void {
        Route::get('login', [LoginController::class, 'create'])->name($loginRouteName);
        Route::post('login', [LoginController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('ceemes.login.store');
        Route::post('logout', [LoginController::class, 'destroy'])
            ->middleware('auth')
            ->name('ceemes.logout');
    });
}

$middleware = config('ceemes.admin.middleware', ['web', 'auth']);
$middleware = is_array($middleware) ? $middleware : ['web', 'auth'];
$middleware[] = 'can:'.config('ceemes.admin.gate', 'access-ceemes');

Route::prefix($adminPrefix)
    ->middleware($middleware)
    ->name('ceemes.admin.')
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('collections', [CollectionController::class, 'index'])->name('collections.index');
        Route::post('collections', [CollectionController::class, 'store'])->name('collections.store');
        Route::put('collections/{collection}', [CollectionController::class, 'update'])->name('collections.update');
        Route::delete('collections/{collection}', [CollectionController::class, 'destroy'])->name('collections.destroy');

        Route::get('collections/{collection}/blueprints', [BlueprintController::class, 'index'])->name('blueprints.index');
        Route::post('collections/{collection}/blueprints', [BlueprintController::class, 'store'])->name('blueprints.store');
        Route::put('blueprints/{blueprint}', [BlueprintController::class, 'update'])->name('blueprints.update');
        Route::delete('blueprints/{blueprint}', [BlueprintController::class, 'destroy'])->name('blueprints.destroy');
        Route::get('blueprints/{blueprint}/fields', [FieldController::class, 'index'])->name('fields.index');
        Route::post('blueprints/{blueprint}/fields', [FieldController::class, 'store'])->name('fields.store');
        Route::put('fields/{field}', [FieldController::class, 'update'])->name('fields.update');
        Route::delete('fields/{field}', [FieldController::class, 'destroy'])->name('fields.destroy');

        Route::get('collections/{collection}/entries', [EntryController::class, 'index'])->name('entries.index');
        Route::get('collections/{collection}/entries/create', [EntryController::class, 'create'])->name('entries.create');
        Route::post('collections/{collection}/entries', [EntryController::class, 'store'])->name('entries.store');
        Route::get('entries/{entry}/edit', [EntryController::class, 'edit'])->name('entries.edit');
        Route::put('entries/{entry}', [EntryController::class, 'update'])->name('entries.update');
        Route::delete('entries/{entry}', [EntryController::class, 'destroy'])->name('entries.destroy');

        Route::post('entries/{entry}/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::put('sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::post('sections/{section}/duplicate', [SectionController::class, 'duplicate'])->name('sections.duplicate');
        Route::post('sections/{section}/toggle', [SectionController::class, 'toggle'])->name('sections.toggle');
        Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');

        Route::get('section-types', [SectionTypeController::class, 'index'])->name('section-types.index');
        Route::post('section-types', [SectionTypeController::class, 'store'])->name('section-types.store');
        Route::put('section-types/{sectionType}', [SectionTypeController::class, 'update'])->name('section-types.update');
        Route::delete('section-types/{sectionType}', [SectionTypeController::class, 'destroy'])->name('section-types.destroy');
        Route::get('section-types/{sectionType}/fields', [SectionFieldController::class, 'index'])->name('section-fields.index');
        Route::post('section-types/{sectionType}/fields', [SectionFieldController::class, 'store'])->name('section-fields.store');
        Route::put('section-fields/{sectionField}', [SectionFieldController::class, 'update'])->name('section-fields.update');
        Route::delete('section-fields/{sectionField}', [SectionFieldController::class, 'destroy'])->name('section-fields.destroy');

        Route::get('taxonomies', [TaxonomyController::class, 'index'])->name('taxonomies.index');
        Route::post('taxonomies', [TaxonomyController::class, 'store'])->name('taxonomies.store');
        Route::put('taxonomies/{taxonomy}', [TaxonomyController::class, 'update'])->name('taxonomies.update');
        Route::delete('taxonomies/{taxonomy}', [TaxonomyController::class, 'destroy'])->name('taxonomies.destroy');
        Route::get('taxonomies/{taxonomy}/terms', [TermController::class, 'index'])->name('terms.index');
        Route::post('taxonomies/{taxonomy}/terms', [TermController::class, 'store'])->name('terms.store');
        Route::put('terms/{term}', [TermController::class, 'update'])->name('terms.update');
        Route::delete('terms/{term}', [TermController::class, 'destroy'])->name('terms.destroy');

        Route::get('navigations', [NavigationController::class, 'index'])->name('navigations.index');
        Route::post('navigations', [NavigationController::class, 'store'])->name('navigations.store');
        Route::put('navigations/{navigation}', [NavigationController::class, 'update'])->name('navigations.update');
        Route::delete('navigations/{navigation}', [NavigationController::class, 'destroy'])->name('navigations.destroy');
        Route::get('navigations/{navigation}/items', [NavigationItemController::class, 'index'])->name('navigation-items.index');
        Route::post('navigations/{navigation}/items', [NavigationItemController::class, 'store'])->name('navigation-items.store');
        Route::put('navigation-items/{navigationItem}', [NavigationItemController::class, 'update'])->name('navigation-items.update');
        Route::delete('navigation-items/{navigationItem}', [NavigationItemController::class, 'destroy'])->name('navigation-items.destroy');

        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'store'])->name('settings.store');
    });
