<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Actions\Sets\CreateSet;
use LaraCeemes\Actions\Sets\CreateSetField;
use LaraCeemes\Enums\ContentStatus;
use LaraCeemes\Tests\TestCase;

final class PublicRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_entries_resolve_homepage_and_nested_public_urls(): void
    {
        $set = app(CreateSet::class)->execute([
            'name' => 'Pages',
            'route' => '/{slug}',
        ]);
        app(CreateSetField::class)->execute($set, [
            'label' => 'Headline',
            'type' => 'text',
        ]);

        app(CreateContent::class)->execute($set, [
            'title' => 'Homepage',
            'uri' => '/',
            'status' => ContentStatus::Published->value,
            'data' => ['headline' => 'Welcome to our website'],
        ]);
        $contact = app(CreateContent::class)->execute($set, [
            'title' => 'Contact',
            'status' => ContentStatus::Published->value,
            'data' => ['headline' => 'Talk to our team'],
        ]);
        app(CreateContent::class)->execute($set, [
            'title' => 'Secret',
            'uri' => '/company/secret',
            'status' => ContentStatus::Draft->value,
        ]);

        self::assertSame('/contact', $contact->uri);

        $this->get('/')->assertOk()->assertSee('Homepage')->assertSee('Welcome to our website');
        $this->get('/contact')->assertOk()->assertSee('Contact')->assertSee('Talk to our team');
        $this->get('/company/secret')->assertNotFound();
        $this->get('/missing')->assertNotFound();
    }

    public function test_public_urls_are_unique_and_cannot_use_the_admin_prefix(): void
    {
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);
        app(CreateContent::class)->execute($set, ['title' => 'Contact', 'uri' => '/contact']);

        $this->expectException(ValidationException::class);
        app(CreateContent::class)->execute($set, ['title' => 'Another Contact', 'uri' => '/contact']);
    }

    public function test_public_url_cannot_shadow_the_admin_area(): void
    {
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);
        $this->expectException(ValidationException::class);
        app(CreateContent::class)->execute($set, ['title' => 'Fake Admin', 'uri' => '/admin/settings']);
    }
}
