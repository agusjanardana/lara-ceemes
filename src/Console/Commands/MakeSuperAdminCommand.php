<?php

declare(strict_types=1);

namespace LaraCeemes\Console\Commands;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use LogicException;

final class MakeSuperAdminCommand extends CeemesCommand
{
    protected $signature = 'ceemes:make-superadmin
        {email? : Email address of the superadmin}
        {--name= : Display name for a new user}
        {--password= : Password for a new user}
        {--force : Update the name and password when the user already exists}';

    protected $description = 'Create or promote an application user as a Lara Ceemes superadmin';

    public function handle(): int
    {
        $model = $this->userModel();

        if ($model === null) {
            return self::FAILURE;
        }

        $usersTable = (string) config('ceemes.users.table', $model->getTable());
        $roleAttribute = (string) config('ceemes.users.role_attribute', 'role');

        if (! Schema::hasTable($usersTable) || ! Schema::hasColumn($usersTable, $roleAttribute)) {
            $this->components->error(
                "Column [{$usersTable}.{$roleAttribute}] is unavailable. Run [php artisan migrate] or configure ceemes.users.role_attribute first.",
            );

            return self::FAILURE;
        }

        $email = trim((string) ($this->argument('email') ?: $this->ask('Email')));
        $emailAttribute = (string) config('ceemes.users.email_attribute', 'email');
        $nameAttribute = (string) config('ceemes.users.name_attribute', 'name');
        $passwordAttribute = (string) config('ceemes.users.password_attribute', 'password');

        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email']]);

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

        $created = $user === null;
        $updatesCredentials = $created || (bool) $this->option('force');
        $name = '';
        $password = $this->stringOption('password') ?? '';

        if ($updatesCredentials) {
            $name = trim((string) ($this->option('name') ?: $this->ask('Name', 'Super Admin')));
            $nameValidator = Validator::make(['name' => $name], ['name' => ['required', 'string', 'max:255']]);

            if ($nameValidator->fails()) {
                foreach ($nameValidator->errors()->all() as $error) {
                    $this->components->error($error);
                }

                return self::FAILURE;
            }

            $password = $password !== '' ? $password : (string) $this->secret('Password');

            if (mb_strlen($password) < 8) {
                $this->components->error('The password must contain at least 8 characters.');

                return self::FAILURE;
            }
        }

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
            $roleAttribute,
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
                    $roleAttribute => config('ceemes.users.superadmin_role', 'superadmin'),
                ])->save();
            } elseif ((bool) $this->option('force')) {
                $user->forceFill([
                    $nameAttribute => $name,
                    $passwordAttribute => Hash::make($password),
                    $roleAttribute => config('ceemes.users.superadmin_role', 'superadmin'),
                ])->save();
            } else {
                $user->forceFill([
                    $roleAttribute => config('ceemes.users.superadmin_role', 'superadmin'),
                ])->save();
            }
        });

        $action = $created ? 'created and promoted' : 'promoted';
        $this->components->success("User [{$email}] {$action} as Lara Ceemes superadmin.");

        if (! $created && ! (bool) $this->option('force')) {
            $this->components->info('The existing name and password were preserved. Use --force to replace them.');
        }

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
