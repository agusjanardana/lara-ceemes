<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Blueprints\CreateBlueprint;
use LaraCeemes\Actions\Blueprints\CreateBlueprintField;
use LaraCeemes\Actions\Blueprints\ReorderBlueprintFields;
use LaraCeemes\Actions\Collections\CreateCollection;
use LaraCeemes\Actions\Collections\DeleteCollection;
use LaraCeemes\Actions\Collections\UpdateCollection;
use LaraCeemes\Actions\Entries\CreateEntry;
use LaraCeemes\Actions\Entries\DeleteEntry;
use LaraCeemes\Actions\Entries\UpdateEntry;
use LaraCeemes\Enums\EntryStatus;
use LaraCeemes\Events\EntryPublished;
use LaraCeemes\Exceptions\StructureInUse;
use LaraCeemes\Facades\Collection as CollectionFacade;
use LaraCeemes\Facades\Entry as EntryFacade;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\Entry;
use LaraCeemes\Tests\TestCase;

final class ContentCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_blueprint_and_ordered_fields_can_be_created(): void
    {
        [$collection, $blueprint] = $this->makeBlueprint();
        $createField = $this->app->make(CreateBlueprintField::class);

        $subtitle = $createField->execute($blueprint, [
            'label' => 'Subtitle',
            'type' => 'textarea',
            'sort_order' => 20,
        ]);
        $headline = $createField->execute($blueprint, [
            'label' => 'Headline',
            'type' => 'text',
            'sort_order' => 10,
        ]);

        self::assertSame('pages', $collection->handle);
        self::assertSame('landing-page', $blueprint->handle);
        self::assertSame(
            [$headline->uuid, $subtitle->uuid],
            $blueprint->fields()->pluck('uuid')->all(),
        );

        $this->app->make(ReorderBlueprintFields::class)->execute($blueprint, [
            $subtitle->uuid,
            $headline->uuid,
        ]);

        self::assertSame(
            [$subtitle->uuid, $headline->uuid],
            $blueprint->fields()->pluck('uuid')->all(),
        );
    }

    public function test_handles_and_entry_slugs_are_unique_in_their_scopes(): void
    {
        [$collection, $blueprint] = $this->makeBlueprint();
        $createEntry = $this->app->make(CreateEntry::class);
        $createEntry->execute($blueprint, ['title' => 'Home']);

        try {
            $this->app->make(CreateCollection::class)->execute(['name' => 'Pages']);
            self::fail('Duplicate Collection handle was accepted.');
        } catch (ValidationException) {
            self::assertTrue(true);
        }

        $this->expectException(ValidationException::class);

        $createEntry->execute($blueprint, [
            'title' => 'Another Home',
            'slug' => 'home',
        ]);
    }

    public function test_entry_data_is_validated_normalized_and_exposed_through_value_api(): void
    {
        [, $blueprint] = $this->makeBlueprint();
        $createField = $this->app->make(CreateBlueprintField::class);
        $createField->execute($blueprint, [
            'handle' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
            'config' => ['required' => true],
        ]);
        $createField->execute($blueprint, [
            'handle' => 'featured',
            'label' => 'Featured',
            'type' => 'boolean',
        ]);

        $entry = $this->app->make(CreateEntry::class)->execute($blueprint, [
            'title' => 'Home',
            'data' => [
                'headline' => '  Welcome  ',
                'featured' => '1',
            ],
            'seo' => [
                'title' => 'Home SEO',
                'description' => 'Home description',
            ],
        ]);

        self::assertSame('Welcome', $entry->get('headline'));
        self::assertTrue($entry->get('featured'));
        self::assertTrue($entry->has('headline'));
        self::assertSame('fallback', $entry->get('missing', 'fallback'));
        self::assertSame('Home SEO', $entry->seo()['title']);
    }

    public function test_unknown_or_invalid_blueprint_data_is_rejected(): void
    {
        [, $blueprint] = $this->makeBlueprint();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unknown fields: arbitrary_html.');

        $this->app->make(CreateEntry::class)->execute($blueprint, [
            'title' => 'Home',
            'data' => ['arbitrary_html' => '<h1>Unsafe structure</h1>'],
        ]);
    }

    public function test_published_query_uses_status_only_and_facades_resolve_content(): void
    {
        Event::fake([EntryPublished::class]);
        [, $blueprint] = $this->makeBlueprint();
        $createEntry = $this->app->make(CreateEntry::class);
        $draft = $createEntry->execute($blueprint, ['title' => 'Draft Page']);
        $published = $createEntry->execute($blueprint, [
            'title' => 'Published Page',
            'status' => EntryStatus::Published->value,
        ]);

        self::assertSame([$published->uuid], CollectionFacade::query('pages')->published()->get()->pluck('uuid')->all());
        self::assertSame([$draft->uuid], CollectionFacade::query('pages')->draft()->get()->pluck('uuid')->all());
        self::assertSame($published->uuid, EntryFacade::find('pages', 'published-page')?->uuid);
        self::assertSame($published->uuid, EntryFacade::byUuid($published->uuid)?->uuid);
        Event::assertDispatched(EntryPublished::class, fn (EntryPublished $event): bool => $event->entry->is($published));
    }

    public function test_entry_updates_invalidate_cache_and_dispatch_publish_event(): void
    {
        Event::fake([EntryPublished::class]);
        [, $blueprint] = $this->makeBlueprint();
        $entry = $this->app->make(CreateEntry::class)->execute($blueprint, ['title' => 'Home']);

        self::assertNotNull(EntryFacade::find('pages', 'home'));

        $updated = $this->app->make(UpdateEntry::class)->execute($entry, [
            'title' => 'Homepage',
            'slug' => 'homepage',
            'status' => EntryStatus::Published->value,
        ]);

        self::assertNull(EntryFacade::find('pages', 'home'));
        self::assertSame('Homepage', EntryFacade::findOrFail('pages', 'homepage')->title);
        Event::assertDispatched(EntryPublished::class, fn (EntryPublished $event): bool => $event->entry->is($updated));
    }

    public function test_entries_are_soft_deleted_and_structures_in_use_are_protected(): void
    {
        [$collection, $blueprint] = $this->makeBlueprint();
        $entry = $this->app->make(CreateEntry::class)->execute($blueprint, ['title' => 'Home']);
        $this->app->make(DeleteEntry::class)->execute($entry);

        self::assertNull(Entry::query()->find($entry->uuid));
        self::assertNotNull(Entry::withTrashed()->find($entry->uuid));

        $this->expectException(StructureInUse::class);
        $this->app->make(DeleteCollection::class)->execute($collection);
    }

    public function test_collection_manager_cache_is_invalidated_after_update(): void
    {
        [$collection] = $this->makeBlueprint();

        self::assertSame('Pages', CollectionFacade::get('pages')->name);
        $this->app->make(UpdateCollection::class)->execute($collection, ['name' => 'Website Pages']);

        self::assertSame('Website Pages', CollectionFacade::get('pages')->name);
        self::assertTrue(CollectionFacade::exists('pages'));
        self::assertCount(1, CollectionFacade::all());
    }

    /** @return array{Collection, Blueprint} */
    private function makeBlueprint(): array
    {
        $collection = $this->app->make(CreateCollection::class)->execute([
            'name' => 'Pages',
        ]);
        $blueprint = $this->app->make(CreateBlueprint::class)->execute($collection, [
            'name' => 'Landing Page',
        ]);

        return [$collection, $blueprint];
    }
}
