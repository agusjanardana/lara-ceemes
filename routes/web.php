<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use LaraCeemes\Http\Controllers\Admin\CategoryController;
use LaraCeemes\Http\Controllers\Admin\CategoryGroupController;
use LaraCeemes\Http\Controllers\Admin\ContentController;
use LaraCeemes\Http\Controllers\Admin\DashboardController;
use LaraCeemes\Http\Controllers\Admin\FieldController;
use LaraCeemes\Http\Controllers\Admin\MediaController;
use LaraCeemes\Http\Controllers\Admin\MediaFolderController;
use LaraCeemes\Http\Controllers\Admin\NavigationController;
use LaraCeemes\Http\Controllers\Admin\NavigationItemController;
use LaraCeemes\Http\Controllers\Admin\SectionController;
use LaraCeemes\Http\Controllers\Admin\SectionFieldController;
use LaraCeemes\Http\Controllers\Admin\SectionLibraryController;
use LaraCeemes\Http\Controllers\Admin\SectionPlacementController;
use LaraCeemes\Http\Controllers\Admin\SectionTypeController;
use LaraCeemes\Http\Controllers\Admin\SetController;
use LaraCeemes\Http\Controllers\Admin\SettingController;
use LaraCeemes\Http\Controllers\Admin\SiteController;
use LaraCeemes\Http\Controllers\Admin\SwitchSiteController;
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
        Route::post('sites/switch', SwitchSiteController::class)->name('sites.switch');
        Route::get('sites', [SiteController::class, 'index'])->name('sites.index');
        Route::post('sites', [SiteController::class, 'store'])->name('sites.store');
        Route::put('sites/{site}', [SiteController::class, 'update'])->name('sites.update');
        Route::delete('sites/{site}', [SiteController::class, 'destroy'])->name('sites.destroy');

        Route::get('sets', [SetController::class, 'index'])->name('sets.index');
        Route::post('sets', [SetController::class, 'store'])->name('sets.store');
        Route::put('sets/{set}', [SetController::class, 'update'])->name('sets.update');
        Route::delete('sets/{set}', [SetController::class, 'destroy'])->name('sets.destroy');
        Route::post('sets/{set}/fields', [FieldController::class, 'store'])->name('set-fields.store');
        Route::put('set-fields/{field}', [FieldController::class, 'update'])->name('set-fields.update');
        Route::delete('set-fields/{field}', [FieldController::class, 'destroy'])->name('set-fields.destroy');

        Route::get('sets/{set}/contents', [ContentController::class, 'index'])->name('contents.index');
        Route::get('sets/{set}/contents/create', [ContentController::class, 'create'])->name('contents.create');
        Route::post('sets/{set}/contents', [ContentController::class, 'store'])->name('contents.store');
        Route::get('contents/{content}/edit', [ContentController::class, 'edit'])->name('contents.edit');
        Route::put('contents/{content}', [ContentController::class, 'update'])->name('contents.update');
        Route::delete('contents/{content}', [ContentController::class, 'destroy'])->name('contents.destroy');

        Route::post('contents/{content}/sections', [SectionController::class, 'store'])->name('sections.store');
        Route::get('sections', SectionLibraryController::class)->name('sections.index');
        Route::post('contents/{content}/section-placements', [SectionPlacementController::class, 'store'])->name('section-placements.store');
        Route::delete('contents/{content}/section-placements/{section}', [SectionPlacementController::class, 'destroy'])->name('section-placements.destroy');
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

        Route::get('category-groups', [CategoryGroupController::class, 'index'])->name('category-groups.index');
        Route::post('category-groups', [CategoryGroupController::class, 'store'])->name('category-groups.store');
        Route::put('category-groups/{categoryGroup}', [CategoryGroupController::class, 'update'])->name('category-groups.update');
        Route::delete('category-groups/{categoryGroup}', [CategoryGroupController::class, 'destroy'])->name('category-groups.destroy');
        Route::get('category-groups/{categoryGroup}/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('category-groups/{categoryGroup}/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('navigations', [NavigationController::class, 'index'])->name('navigations.index');
        Route::post('navigations', [NavigationController::class, 'store'])->name('navigations.store');
        Route::put('navigations/{navigation}', [NavigationController::class, 'update'])->name('navigations.update');
        Route::delete('navigations/{navigation}', [NavigationController::class, 'destroy'])->name('navigations.destroy');
        Route::get('navigations/{navigation}/items', [NavigationItemController::class, 'index'])->name('navigation-items.index');
        Route::post('navigations/{navigation}/items', [NavigationItemController::class, 'store'])->name('navigation-items.store');
        Route::put('navigation-items/{navigationItem}', [NavigationItemController::class, 'update'])->name('navigation-items.update');
        Route::delete('navigation-items/{navigationItem}', [NavigationItemController::class, 'destroy'])->name('navigation-items.destroy');

        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media-folders', [MediaFolderController::class, 'store'])->name('media-folders.store');
        Route::delete('media-folders/{mediaFolder}', [MediaFolderController::class, 'destroy'])->name('media-folders.destroy');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'store'])->name('settings.store');
    });
