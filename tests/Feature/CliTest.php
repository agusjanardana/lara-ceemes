<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Models\BlueprintField;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\SectionField;
use LaraCeemes\Models\SectionType;
use LaraCeemes\Models\Setting;
use LaraCeemes\Models\Taxonomy;
use LaraCeemes\Models\Term;
use LaraCeemes\Tests\Fixtures\User;
use LaraCeemes\Tests\TestCase;

final class CliTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_structure_commands_use_the_application_actions(): void
    {
        $this->artisan('ceemes:make-collection', [
            'handle' => 'pages',
            '--name' => 'Website Pages',
            '--route' => '/{slug}',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-blueprint', [
            'handle' => 'landing-page',
            '--collection' => 'pages',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-field', [
            'handle' => 'headline',
            '--blueprint' => 'landing-page',
            '--type' => 'text',
        ])->assertSuccessful();

        self::assertSame('Website Pages', Collection::query()->sole()->name);
        self::assertSame('landing-page', Blueprint::query()->sole()->handle);
        self::assertSame('headline', BlueprintField::query()->sole()->handle);
    }

    public function test_entry_command_creates_entry_with_json_data(): void
    {
        $this->artisan('ceemes:make-collection', ['handle' => 'pages'])->assertSuccessful();
        $this->artisan('ceemes:make-blueprint', [
            'handle' => 'page',
            '--collection' => 'pages',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-field', [
            'handle' => 'headline',
            '--blueprint' => 'page',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-entry', [
            '--collection' => 'pages',
            '--blueprint' => 'page',
            '--title' => 'Home',
            '--status' => 'published',
            '--data' => '{"headline":"Welcome"}',
        ])->assertSuccessful();

        self::assertSame('Welcome', Entry::query()->sole()->get('headline'));
    }

    public function test_section_taxonomy_term_and_navigation_commands_work(): void
    {
        $this->artisan('ceemes:make-section', ['handle' => 'banner'])->assertSuccessful();
        $this->artisan('ceemes:make-section-field', [
            'handle' => 'title',
            '--section' => 'banner',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-taxonomy', ['handle' => 'categories'])->assertSuccessful();
        $this->artisan('ceemes:make-term', [
            'slug' => 'technology',
            '--taxonomy' => 'categories',
        ])->assertSuccessful();
        $this->artisan('ceemes:make-navigation', ['handle' => 'header'])->assertSuccessful();

        self::assertSame('banner', SectionType::query()->sole()->handle);
        self::assertSame('title', SectionField::query()->sole()->handle);
        self::assertSame('categories', Taxonomy::query()->sole()->handle);
        self::assertSame('technology', Term::query()->sole()->slug);
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
