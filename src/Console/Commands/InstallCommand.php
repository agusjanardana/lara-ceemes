<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use LaraCeemes\Actions\Navigations\CreateNavigation;
use LaraCeemes\Actions\Sets\CreateSet;
use LaraCeemes\Actions\Settings\SetSetting;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\Set;
use LaraCeemes\Models\Setting;
use LaraCeemes\Support\SetViewScaffolder;

final class InstallCommand extends Command
{
    protected $signature = 'ceemes:install {--force : Overwrite published configuration}';

    protected $description = 'Install Lara Ceemes and create its minimum default data';

    public function handle(
        CreateSet $createSet,
        CreateNavigation $createNavigation,
        SetSetting $setSetting,
        SetViewScaffolder $views,
    ): int {
        $this->components->info('Installing Lara Ceemes');

        $publishParameters = ['--tag' => 'ceemes-config'];

        if ((bool) $this->option('force')) {
            $publishParameters['--force'] = true;
        }

        $this->callSilent('vendor:publish', $publishParameters);
        $this->callSilent('vendor:publish', [
            '--tag' => 'ceemes-assets',
            '--force' => true,
        ]);
        $this->call('migrate', ['--force' => true]);

        if (! Set::query()->where('handle', 'pages')->exists()) {
            $pages = $createSet->execute(['name' => 'Pages', 'handle' => 'pages']);
        } else {
            $pages = Set::query()->where('handle', 'pages')->sole();
            $pages->update([
                'route' => $pages->route ?: $views->routePattern('pages'),
                'template' => $pages->template ?: $views->viewName('pages'),
            ]);
            $views->scaffold($pages->refresh());
        }

        foreach (['Header', 'Footer'] as $name) {
            $handle = strtolower($name);

            if (! Navigation::query()->where('handle', $handle)->exists()) {
                $createNavigation->execute(['name' => $name, 'handle' => $handle]);
            }
        }

        $defaults = [
            'general.site_name' => config('app.name', 'Laravel'),
            'seo.title_separator' => '|',
            'seo.default_robots_index' => true,
            'seo.default_robots_follow' => true,
            'cache.enabled' => true,
            'cache.ttl' => (int) config('ceemes.cache.ttl', 3600),
            'media.disk' => (string) config('ceemes.media.disk', 'public'),
        ];

        foreach ($defaults as $key => $value) {
            [$group, $settingKey] = explode('.', $key, 2);

            if (! Setting::query()->where('group', $group)->where('key', $settingKey)->exists()) {
                $setSetting->execute($key, $value, autoload: true);
            }
        }

        $disk = (string) config('ceemes.media.disk', 'public');
        Storage::disk($disk);

        $this->newLine();
        $adminPath = '/'.trim((string) config('ceemes.admin.prefix', 'admin'), '/');
        $this->components->success("Lara Ceemes is ready. Create an admin with [php artisan ceemes:make-superadmin], then open {$adminPath}.");
        $this->components->info('Optional: run [php artisan ceemes:make-agent-skill] and commit the generated project skill for coding agents.');

        return self::SUCCESS;
    }
}
