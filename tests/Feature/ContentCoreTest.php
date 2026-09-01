<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Actions\Contents\DeleteContent;
use LaraCeemes\Actions\Contents\UpdateContent;
use LaraCeemes\Actions\Sets\CreateSet;
use LaraCeemes\Actions\Sets\CreateSetField;
use LaraCeemes\Actions\Sets\DeleteSet;
use LaraCeemes\Actions\Sets\ReorderSetFields;
use LaraCeemes\Actions\Sets\UpdateSet;
use LaraCeemes\Enums\ContentStatus;
use LaraCeemes\Events\ContentPublished;
use LaraCeemes\Exceptions\StructureInUse;
use LaraCeemes\Facades\Content as ContentFacade;
use LaraCeemes\Facades\Content as EntryFacade;
use LaraCeemes\Facades\Set as CollectionFacade;
use LaraCeemes\Facades\Set as SetFacade;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Set;
use LaraCeemes\Tests\TestCase;

final class ContentCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_and_ordered_fields_can_be_created(): void
    {
        $set = $this->makeSet();
        $createField = $this->app->make(CreateSetField::class);

        $subtitle = $createField->execute($set, [
            'label' => 'Subtitle',
            'type' => 'textarea',
            'sort_order' => 20,
        ]);
        $headline = $createField->execute($set, [
            'label' => 'Headline',
            'type' => 'text',
            'sort_order' => 10,
        ]);

        self::assertSame('pages', $set->handle);
        self::assertSame(
            [$headline->uuid, $subtitle->uuid],
            $set->fields()->pluck('uuid')->all(),
        );

        $this->app->make(ReorderSetFields::class)->execute($set, [
            $subtitle->uuid,
            $headline->uuid,
        ]);

        self::assertSame(
            [$subtitle->uuid, $headline->uuid],
            $set->fields()->pluck('uuid')->all(),
        );
    }

    public function test_handles_and_content_slugs_are_unique_in_their_scopes(): void
    {
        $set = $this->makeSet();
        $createEntry = $this->app->make(CreateContent::class);
        $createEntry->execute($set, ['title' => 'Home']);

        try {
            $this->app->make(CreateSet::class)->execute(['name' => 'Pages']);
            self::fail('Duplicate Set handle was accepted.');
        } catch (ValidationException) {
            self::assertTrue(true);
        }

        $this->expectException(ValidationException::class);

        $createEntry->execute($set, [
            'title' => 'Another Home',
            'slug' => 'home',
        ]);
    }

    public function test_content_data_is_validated_normalized_and_exposed_through_value_api(): void
    {
        $set = $this->makeSet();
        $createField = $this->app->make(CreateSetField::class);
        $createField->execute($set, [
            'handle' => 'headline',
            'label' => 'Headline',
            'type' => 'text',
            'config' => ['required' => true],
        ]);
        $createField->execute($set, [
            'handle' => 'featured',
            'label' => 'Featured',
            'type' => 'boolean',
        ]);

        $content = $this->app->make(CreateContent::class)->execute($set, [
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

        self::assertSame('Welcome', $content->get('headline'));
        self::assertTrue($content->get('featured'));
        self::assertTrue($content->has('headline'));
        self::assertSame('fallback', $content->get('missing', 'fallback'));
        self::assertSame('Home SEO', $content->seo()['title']);
    }

    public function test_unknown_or_invalid_set_data_is_rejected(): void
    {
        $set = $this->makeSet();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unknown fields: arbitrary_html.');

        $this->app->make(CreateContent::class)->execute($set, [
            'title' => 'Home',
            'data' => ['arbitrary_html' => '<h1>Unsafe structure</h1>'],
        ]);
    }

    public function test_published_query_uses_status_only_and_facades_resolve_content(): void
    {
        Event::fake([ContentPublished::class]);
        $set = $this->makeSet();
        $createEntry = $this->app->make(CreateContent::class);
        $draft = $createEntry->execute($set, ['title' => 'Draft Page']);
        $published = $createEntry->execute($set, [
            'title' => 'Published Page',
            'status' => ContentStatus::Published->value,
        ]);

        self::assertSame([$published->uuid], CollectionFacade::query('pages')->published()->get()->pluck('uuid')->all());
        self::assertSame([$draft->uuid], CollectionFacade::query('pages')->draft()->get()->pluck('uuid')->all());
        self::assertSame($published->uuid, EntryFacade::find('pages', 'published-page')?->uuid);
        self::assertSame($published->uuid, EntryFacade::byUuid($published->uuid)?->uuid);
        self::assertSame($published->uuid, ContentFacade::find('pages', 'published-page')?->uuid);
        self::assertSame($published->uuid, SetFacade::query('pages')->published()->first()?->uuid);
        Event::assertDispatched(ContentPublished::class, fn (ContentPublished $event): bool => $event->content->is($published));
    }

    public function test_content_updates_invalidate_cache_and_dispatch_publish_event(): void
    {
        Event::fake([ContentPublished::class]);
        $set = $this->makeSet();
        $content = $this->app->make(CreateContent::class)->execute($set, ['title' => 'Home']);

        self::assertNotNull(EntryFacade::find('pages', 'home'));

        $updated = $this->app->make(UpdateContent::class)->execute($content, [
            'title' => 'Homepage',
            'slug' => 'homepage',
            'status' => ContentStatus::Published->value,
        ]);

        self::assertNull(EntryFacade::find('pages', 'home'));
        self::assertSame('Homepage', EntryFacade::findOrFail('pages', 'homepage')->title);
        Event::assertDispatched(ContentPublished::class, fn (ContentPublished $event): bool => $event->content->is($updated));
    }

    public function test_entries_are_soft_deleted_and_structures_in_use_are_protected(): void
    {
        $set = $this->makeSet();
        $content = $this->app->make(CreateContent::class)->execute($set, ['title' => 'Home']);
        $this->app->make(DeleteContent::class)->execute($content);

        self::assertNull(Content::query()->find($content->uuid));
        self::assertNotNull(Content::withTrashed()->find($content->uuid));

        $this->expectException(StructureInUse::class);
        $this->app->make(DeleteSet::class)->execute($set);
    }

    public function test_set_manager_cache_is_invalidated_after_update(): void
    {
        $set = $this->makeSet();

        self::assertSame('Pages', CollectionFacade::get('pages')->name);
        $this->app->make(UpdateSet::class)->execute($set, ['name' => 'Website Pages']);

        self::assertSame('Website Pages', CollectionFacade::get('pages')->name);
        self::assertTrue(CollectionFacade::exists('pages'));
        self::assertCount(1, CollectionFacade::all());
    }

    private function makeSet(): Set
    {
        return $this->app->make(CreateSet::class)->execute([
            'name' => 'Pages',
        ]);
    }
}
