<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use LaraCeemes\Support\SuperAdminRegistry;
use LogicException;

final class MakeSuperAdminCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-superadmin
        {email? : Email address of the superadmin}
        {--name= : Display name for a new user}
        {--password= : Password for a new user}
        {--force : Update the name and password when the user already exists}';

    protected $description = 'Create or promote an application user as a Lara Ceemes superadmin';

    public function handle(SuperAdminRegistry $registry): int
    {
        if (! Schema::hasTable('ceemes_super_admins')) {
            $this->components->error('Run [php artisan migrate] or [php artisan ceemes:install] first.');

            return self::FAILURE;
        }

        $model = $this->userModel();

        if ($model === null) {
            return self::FAILURE;
        }

        $email = trim((string) ($this->argument('email') ?: $this->ask('Email')));
        $name = trim((string) ($this->option('name') ?: $this->ask('Name', 'Super Admin')));
        $emailAttribute = (string) config('ceemes.users.email_attribute', 'email');
        $nameAttribute = (string) config('ceemes.users.name_attribute', 'name');
        $passwordAttribute = (string) config('ceemes.users.password_attribute', 'password');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name],
            ['email' => ['required', 'email'], 'name' => ['required', 'string', 'max:255']],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user = $model->newQuery()->where($emailAttribute, $email)->first();

        if ($user !== null && ! $user instanceof Authenticatable) {
            $this->components->error('The resolved User must implement Laravel\'s Authenticatable contract.');

            return self::FAILURE;
        }

        $password = $this->stringOption('password') ?? '';

        if ($user === null || (bool) $this->option('force')) {
            $password = $password !== '' ? $password : (string) $this->secret('Password');

            if (mb_strlen($password) < 8) {
                $this->components->error('The password must contain at least 8 characters.');

                return self::FAILURE;
            }
        }

        $created = $user === null;

        DB::transaction(function () use (
            $user,
            $model,
            $created,
            $email,
            $emailAttribute,
            $name,
            $nameAttribute,
            $password,
            $passwordAttribute,
            $registry,
        ): void {
            if ($created) {
                $user = $model->newInstance();

                if (! $user instanceof Authenticatable) {
                    throw new LogicException('The configured User model must implement Authenticatable.');
                }

                $user->forceFill([
                    $emailAttribute => $email,
                    $nameAttribute => $name,
                    $passwordAttribute => Hash::make($password),
                ])->save();
            } elseif ((bool) $this->option('force')) {
                $user->forceFill([
                    $nameAttribute => $name,
                    $passwordAttribute => Hash::make($password),
                ])->save();
            }

            $registry->promote($user);
        });

        $action = $created ? 'created and promoted' : 'promoted';
        $this->components->success("User [{$email}] {$action} as Lara Ceemes superadmin.");

        return self::SUCCESS;
    }

    private function userModel(): ?Model
    {
        $provider = (string) config('auth.defaults.provider', 'users');
        $modelClass = config('ceemes.users.model')
            ?: config("auth.providers.{$provider}.model");

        if (! is_string($modelClass) || ! class_exists($modelClass)) {
            $this->components->error('The configured application User model could not be resolved.');

            return null;
        }

        $model = app($modelClass);

        if (! $model instanceof Model || ! $model instanceof Authenticatable) {
            $this->components->error('The configured User model must be an authenticatable Eloquent model.');

            return null;
        }

        return $model;
    }
}
