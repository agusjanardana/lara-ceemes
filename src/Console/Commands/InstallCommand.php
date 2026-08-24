<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use LaraCeemes\Actions\Collections\CreateCollection;
use LaraCeemes\Actions\Navigations\CreateNavigation;
use LaraCeemes\Actions\Settings\SetSetting;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\Setting;

final class InstallCommand extends Command
{
    protected $signature = 'ceemes:install {--force : Overwrite published configuration}';

    protected $description = 'Install Lara Ceemes and create its minimum default data';

    public function handle(
        CreateCollection $createCollection,
        CreateNavigation $createNavigation,
        SetSetting $setSetting,
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

        if (! Collection::query()->where('handle', 'pages')->exists()) {
            $createCollection->execute(['name' => 'Pages', 'handle' => 'pages', 'route' => '/{slug}']);
        } else {
            Collection::query()
                ->where('handle', 'pages')
                ->whereNull('route')
                ->update(['route' => '/{slug}']);
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

        return self::SUCCESS;
    }
}
