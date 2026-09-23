<?php

declare(strict_types=1);

namespace LaraCeemes;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LaraCeemes\Contracts\FieldType;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Fields\Types\BooleanField;
use LaraCeemes\Fields\Types\CategoryField;
use LaraCeemes\Fields\Types\ColorField;
use LaraCeemes\Fields\Types\ContentField;
use LaraCeemes\Fields\Types\DateField;
use LaraCeemes\Fields\Types\DatetimeField;
use LaraCeemes\Fields\Types\EmailField;
use LaraCeemes\Fields\Types\GroupField;
use LaraCeemes\Fields\Types\MediaField;
use LaraCeemes\Fields\Types\NumberField;
use LaraCeemes\Fields\Types\RepeaterField;
use LaraCeemes\Fields\Types\RichtextField;
use LaraCeemes\Fields\Types\SectionsField;
use LaraCeemes\Fields\Types\SelectField;
use LaraCeemes\Fields\Types\SeoField;
use LaraCeemes\Fields\Types\TextareaField;
use LaraCeemes\Fields\Types\TextField;
use LaraCeemes\Fields\Types\UrlField;
use LaraCeemes\Http\Controllers\HomeController;
use LaraCeemes\Http\Controllers\PublicContentController;
use LaraCeemes\Managers\CategoryManager;
use LaraCeemes\Managers\ContentManager;
use LaraCeemes\Managers\MediaManager;
use LaraCeemes\Managers\NavigationManager;
use LaraCeemes\Managers\SectionManager;
use LaraCeemes\Managers\SeoManager;
use LaraCeemes\Managers\SetManager;
use LaraCeemes\Managers\SettingManager;
use LaraCeemes\Support\CeemesCache;
use LaraCeemes\Support\UserRoleAuthorizer;

final class LaraCeemesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ceemes.php',
            'ceemes',
        );

        $this->app->singleton(CeemesCache::class);
        $this->app->singleton(UserRoleAuthorizer::class);
        $this->app->singleton(SetManager::class);
        $this->app->singleton(ContentManager::class);
        $this->app->singleton(MediaManager::class);
        $this->app->singleton(CategoryManager::class);
        $this->app->singleton(NavigationManager::class);
        $this->app->singleton(SettingManager::class);
        $this->app->singleton(SeoManager::class);
        $this->app->singleton(SectionManager::class);
        $this->app->singleton(FieldRegistry::class, function ($app): FieldRegistry {
            $registry = new FieldRegistry($app);

            foreach ($this->coreFieldTypes() as $fieldType) {
                $registry->register($fieldType);
            }

            return $registry;
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ceemes');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        if (! $this->app->routesAreCached()) {
            $this->app->booted(function (): void {
                $publicRouting = (bool) config('ceemes.public_routing.enabled', true);
                $homepage = (bool) config('ceemes.homepage.enabled', true);
                $publicMiddleware = config('ceemes.public_routing.middleware', ['web']);
                $publicMiddleware = is_array($publicMiddleware) ? $publicMiddleware : ['web'];

                if ($publicRouting) {
                    Route::middleware($publicMiddleware)->group(function (): void {
                        Route::get('/{ceemesPath}', PublicContentController::class)
                            ->where('ceemesPath', '.*')
                            ->name('ceemes.content.show');
                    });

                    if ($homepage) {
                        Route::middleware($publicMiddleware)->get('/', PublicContentController::class)->name('ceemes.home');
                    }
                } elseif ($homepage) {
                    Route::middleware($publicMiddleware)->get('/', HomeController::class)->name('ceemes.home');
                }
            });
        }

        if (! Gate::has((string) config('ceemes.admin.gate', 'access-ceemes'))) {
            Gate::define(
                (string) config('ceemes.admin.gate', 'access-ceemes'),
                fn (mixed $user): bool => $this->app->make(UserRoleAuthorizer::class)->allows($user),
            );
        }

        $this->publishes([
            __DIR__.'/../config/ceemes.php' => config_path('ceemes.php'),
        ], 'ceemes-config');

        $this->publishes([
            __DIR__.'/../resources/dist' => public_path('vendor/ceemes'),
        ], 'ceemes-assets');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\InstallCommand::class,
                Console\Commands\StatusCommand::class,
                Console\Commands\ClearCacheCommand::class,
                Console\Commands\MakeSetCommand::class,
                Console\Commands\MakeContentCommand::class,
                Console\Commands\MakeSetFieldCommand::class,
                Console\Commands\MakeSectionTypeCommand::class,
                Console\Commands\MakeSectionFieldCommand::class,
                Console\Commands\MakeCategoryGroupCommand::class,
                Console\Commands\MakeCategoryCommand::class,
                Console\Commands\MakeNavigationCommand::class,
                Console\Commands\GetSettingCommand::class,
                Console\Commands\SetSettingCommand::class,
                Console\Commands\MakeSuperAdminCommand::class,
            ]);
        }
    }

    /**
     * @return array<int, class-string<FieldType>>
     */
    private function coreFieldTypes(): array
    {
        return [
            TextField::class,
            TextareaField::class,
            RichtextField::class,
            NumberField::class,
            BooleanField::class,
            SelectField::class,
            DateField::class,
            DatetimeField::class,
            EmailField::class,
            UrlField::class,
            ColorField::class,
            MediaField::class,
            CategoryField::class,
            ContentField::class,
            GroupField::class,
            RepeaterField::class,
            SectionsField::class,
            SeoField::class,
        ];
    }
}
