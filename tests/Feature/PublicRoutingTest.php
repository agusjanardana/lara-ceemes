<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Blueprints\CreateBlueprint;
use LaraCeemes\Actions\Blueprints\CreateBlueprintField;
use LaraCeemes\Actions\Collections\CreateCollection;
use LaraCeemes\Actions\Entries\CreateEntry;
use LaraCeemes\Enums\EntryStatus;
use LaraCeemes\Tests\TestCase;

final class PublicRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_entries_resolve_homepage_and_nested_public_urls(): void
    {
        $collection = app(CreateCollection::class)->execute([
            'name' => 'Pages',
            'route' => '/{slug}',
        ]);
        $blueprint = app(CreateBlueprint::class)->execute($collection, ['name' => 'Standard Page']);
        app(CreateBlueprintField::class)->execute($blueprint, [
            'label' => 'Headline',
            'type' => 'text',
        ]);

        app(CreateEntry::class)->execute($blueprint, [
            'title' => 'Homepage',
            'uri' => '/',
            'status' => EntryStatus::Published->value,
            'data' => ['headline' => 'Welcome to our website'],
        ]);
        $contact = app(CreateEntry::class)->execute($blueprint, [
            'title' => 'Contact',
            'status' => EntryStatus::Published->value,
            'data' => ['headline' => 'Talk to our team'],
        ]);
        app(CreateEntry::class)->execute($blueprint, [
            'title' => 'Secret',
            'uri' => '/company/secret',
            'status' => EntryStatus::Draft->value,
        ]);

        self::assertSame('/contact', $contact->uri);

        $this->get('/')->assertOk()->assertSee('Homepage')->assertSee('Welcome to our website');
        $this->get('/contact')->assertOk()->assertSee('Contact')->assertSee('Talk to our team');
        $this->get('/company/secret')->assertNotFound();
        $this->get('/missing')->assertNotFound();
    }

    public function test_public_urls_are_unique_and_cannot_use_the_admin_prefix(): void
    {
        $collection = app(CreateCollection::class)->execute(['name' => 'Pages']);
        $blueprint = app(CreateBlueprint::class)->execute($collection, ['name' => 'Page']);
        app(CreateEntry::class)->execute($blueprint, ['title' => 'Contact', 'uri' => '/contact']);

        $this->expectException(ValidationException::class);
        app(CreateEntry::class)->execute($blueprint, ['title' => 'Another Contact', 'uri' => '/contact']);
    }

    public function test_public_url_cannot_shadow_the_admin_area(): void
    {
        $collection = app(CreateCollection::class)->execute(['name' => 'Pages']);
        $blueprint = app(CreateBlueprint::class)->execute($collection, ['name' => 'Page']);

        $this->expectException(ValidationException::class);
        app(CreateEntry::class)->execute($blueprint, ['title' => 'Fake Admin', 'uri' => '/admin/settings']);
    }
}
