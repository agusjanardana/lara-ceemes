<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use LaraCeemes\Tests\Fixtures\User;
use LaraCeemes\Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_login_and_logout_with_package_routes(): void
    {
        $user = User::query()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);

        $this->post('/admin/logout')
            ->assertRedirect('/admin/login');

        $this->assertGuest();
    }

    public function test_regular_user_cannot_login_to_ceemes_admin(): void
    {
        User::query()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $this->from('/admin/login')->post('/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ])->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_package_homepage_replaces_the_default_laravel_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Konten terstruktur. Frontend tetap milik Anda.')
            ->assertSee('Login Admin');
    }
}
