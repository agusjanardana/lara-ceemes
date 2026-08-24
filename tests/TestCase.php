<?php

declare(strict_types=1);

namespace LaraCeemes\Tests;

use LaraCeemes\LaraCeemesServiceProvider;
use LaraCeemes\Tests\Fixtures\User;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaraCeemesServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('ceemes.users.table', 'users');
        $app['config']->set('ceemes.users.model', User::class);
        $app['config']->set('ceemes.users.key', 'id');
        $app['config']->set('ceemes.users.key_type', 'integer');
        $app['config']->set('ceemes.users.foreign_keys', true);
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
