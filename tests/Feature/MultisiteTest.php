<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Actions\Navigations\CreateNavigation;
use LaraCeemes\Actions\Navigations\CreateNavigationItem;
use LaraCeemes\Actions\Sets\CreateSet;
use LaraCeemes\Actions\Sets\CreateSetField;
use LaraCeemes\Actions\Sites\CreateSite;
use LaraCeemes\Enums\ContentStatus;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Models\Site;
use LaraCeemes\Support\SiteContext;
use LaraCeemes\Tests\Fixtures\User;
use LaraCeemes\Tests\TestCase;

final class MultisiteTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('ceemes.multisite.enabled', true);
        $app['config']->set('ceemes.multisite.default_handle', 'en');
        $app['config']->set('ceemes.multisite.default_name', 'English');
        $app['config']->set('ceemes.multisite.default_locale', 'en');
    }

    public function test_public_routes_are_prefixed_and_content_is_isolated_by_site(): void
    {
        $english = Site::query()->where('is_default', true)->sole();
        $indonesian = app(CreateSite::class)->execute([
            'name' => 'Indonesia',
            'handle' => 'id',
            'locale' => 'id',
        ]);
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);
        app(CreateSetField::class)->execute($set, ['label' => 'Headline', 'type' => 'text']);
        $sites = app(SiteContext::class);

        $sites->use($english);
        $englishContact = app(CreateContent::class)->execute($set, [
            'title' => 'Contact',
            'uri' => '/contact',
            'status' => ContentStatus::Published->value,
            'data' => ['headline' => 'Contact our team'],
        ]);

        $sites->use($indonesian);
        $indonesianContact = app(CreateContent::class)->execute($set, [
            'title' => 'Kontak',
            'slug' => 'contact',
            'uri' => '/contact',
            'status' => ContentStatus::Published->value,
            'data' => ['headline' => 'Hubungi tim kami'],
        ]);

        self::assertSame('http://localhost/en/contact', $englishContact->publicUrl());
        self::assertSame('http://localhost/id/contact', $indonesianContact->publicUrl());

        $sites->forget();
        $this->get('/')->assertRedirect('/en');
        $this->get('/en/contact')->assertOk()->assertSee('<html lang="en">', false)->assertSee('Contact our team')->assertDontSee('Hubungi tim kami');
        $this->get('/id/contact')->assertOk()->assertSee('<html lang="id">', false)->assertSee('Hubungi tim kami')->assertDontSee('Contact our team');
        $this->get('/xx/contact')->assertNotFound();
    }

    public function test_model_scope_and_unique_urls_follow_the_active_site(): void
    {
        $english = Site::query()->where('is_default', true)->sole();
        $indonesian = app(CreateSite::class)->execute(['name' => 'Indonesia', 'handle' => 'id', 'locale' => 'id']);
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);
        $sites = app(SiteContext::class);

        $sites->use($english);
        $englishContent = app(CreateContent::class)->execute($set, ['title' => 'About', 'uri' => '/about']);

        try {
            app(CreateContent::class)->execute($set, ['title' => 'Duplicate', 'uri' => '/about']);
            self::fail('Duplicate URI in the same Site should be rejected.');
        } catch (ValidationException) {
            self::assertTrue(true);
        }

        $sites->use($indonesian);
        app(CreateContent::class)->execute($set, ['title' => 'Tentang', 'slug' => 'about', 'uri' => '/about']);
        self::assertSame(1, Content::query()->count());
        self::assertSame('Tentang', Content::query()->sole()->title);
        self::assertSame(2, Content::withoutGlobalScope('ceemes_site')->count());

        $navigation = app(CreateNavigation::class)->execute(['name' => 'Header']);
        try {
            app(CreateNavigationItem::class)->execute($navigation, [
                'label' => 'English About',
                'type' => 'content',
                'target' => $englishContent->uuid,
            ]);
            self::fail('Navigation should not target Content from another Site.');
        } catch (ValidationException) {
            self::assertTrue(true);
        }

        $sites->use($english);
        app(CreateNavigation::class)->execute(['name' => 'Header']);
        self::assertSame(1, Navigation::query()->count());
        self::assertSame(2, Navigation::withoutGlobalScope('ceemes_site')->count());
    }

    public function test_admin_can_switch_the_active_site(): void
    {
        $indonesian = app(CreateSite::class)->execute(['name' => 'Indonesia', 'handle' => 'id', 'locale' => 'id']);
        $user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
        ]);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('data-site-switch', false)
            ->assertSee('English /en')
            ->assertSee('Indonesia /id');

        $this->actingAs($user)
            ->post('/admin/sites/switch', ['site_uuid' => $indonesian->uuid])
            ->assertRedirect()
            ->assertSessionHas('ceemes_site_uuid', $indonesian->uuid);

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('<html lang="id">', false);
    }
}
