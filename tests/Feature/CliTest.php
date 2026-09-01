<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use LaraCeemes\Models\Category;
use LaraCeemes\Models\CategoryGroup;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\SectionField;
use LaraCeemes\Models\SectionType;
use LaraCeemes\Models\Set;
use LaraCeemes\Models\SetField;
use LaraCeemes\Models\Setting;
use LaraCeemes\Tests\Fixtures\User;
use LaraCeemes\Tests\TestCase;

final class CliTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_structure_commands_use_the_application_actions(): void
    {
        $this->artisan('ceemes:make-set', [
            'handle' => 'pages',
            '--name' => 'Website Pages',
            '--route' => '/{slug}',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-set-field', [
            'handle' => 'headline',
            '--set' => 'pages',
            '--type' => 'text',
        ])->assertSuccessful();

        self::assertSame('Website Pages', Set::query()->sole()->name);
        self::assertSame('headline', SetField::query()->sole()->handle);
    }

    public function test_content_command_creates_content_with_json_data(): void
    {
        $this->artisan('ceemes:make-set', ['handle' => 'pages'])->assertSuccessful();
        $this->artisan('ceemes:make-set-field', [
            'handle' => 'headline',
            '--set' => 'pages',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-content', [
            '--set' => 'pages',
            '--title' => 'Home',
            '--status' => 'published',
            '--data' => '{"headline":"Welcome"}',
        ])->assertSuccessful();

        self::assertSame('Welcome', Content::query()->sole()->get('headline'));
    }

    public function test_set_content_commands_use_direct_set_fields(): void
    {
        $this->artisan('ceemes:make-set', ['handle' => 'articles'])->assertSuccessful();
        $this->artisan('ceemes:make-set-field', [
            'handle' => 'excerpt',
            '--set' => 'articles',
            '--type' => 'textarea',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-content', [
            '--set' => 'articles',
            '--title' => 'First Article',
            '--data' => '{"excerpt":"Summary"}',
        ])->assertSuccessful();

        self::assertSame('Summary', Content::query()->sole()->get('excerpt'));
        self::assertSame('excerpt', Set::query()->sole()->fields()->sole()->handle);
    }

    public function test_section_category_and_navigation_commands_work(): void
    {
        $this->artisan('ceemes:make-section', ['handle' => 'banner'])->assertSuccessful();
        $this->artisan('ceemes:make-section-field', [
            'handle' => 'title',
            '--section' => 'banner',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-category-group', ['handle' => 'categories'])->assertSuccessful();
        $this->artisan('ceemes:make-category', [
            'slug' => 'technology',
            '--group' => 'categories',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-navigation', ['handle' => 'header'])->assertSuccessful();

        self::assertSame('banner', SectionType::query()->sole()->handle);
        self::assertSame('title', SectionField::query()->sole()->handle);
        self::assertSame('categories', CategoryGroup::query()->sole()->handle);
        self::assertSame('technology', Category::query()->sole()->slug);
        self::assertSame('header', Navigation::query()->sole()->handle);
    }

    public function test_setting_status_and_cache_commands_work(): void
    {
        $this->artisan('ceemes:setting:set', [
            'key' => 'cache.enabled',
            'value' => 'false',
            '--type' => 'boolean',
        ])->assertSuccessful();
        $this->artisan('ceemes:setting:get', ['key' => 'cache.enabled'])
            ->expectsOutput('false')
            ->assertSuccessful();
        $this->artisan('ceemes:status')->assertSuccessful();
        $this->artisan('ceemes:cache:clear')->assertSuccessful();

        self::assertFalse(Setting::query()->sole()->value);
    }

    public function test_superadmin_command_creates_and_promotes_application_user(): void
    {
        $this->artisan('ceemes:make-superadmin', [
            'email' => 'owner@example.com',
            '--name' => 'Site Owner',
            '--password' => 'password123',
        ])->assertSuccessful();

        $user = User::query()->where('email', 'owner@example.com')->sole();

        self::assertSame('Site Owner', $user->name);
        self::assertTrue(Hash::check('password123', $user->password));
        self::assertTrue(Gate::forUser($user)->allows('access-ceemes'));

        $this->artisan('ceemes:make-superadmin', [
            'email' => 'owner@example.com',
            '--name' => 'Ignored Name',
        ])->assertSuccessful();

        self::assertSame(1, User::query()->count());
    }
}
