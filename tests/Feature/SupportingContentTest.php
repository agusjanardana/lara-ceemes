<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use LaraCeemes\Actions\Blueprints\CreateBlueprint;
use LaraCeemes\Actions\Blueprints\CreateBlueprintField;
use LaraCeemes\Actions\Collections\CreateCollection;
use LaraCeemes\Actions\Entries\CreateEntry;
use LaraCeemes\Actions\Navigations\CreateNavigation;
use LaraCeemes\Actions\Navigations\CreateNavigationItem;
use LaraCeemes\Actions\Navigations\ReorderNavigationItems;
use LaraCeemes\Actions\Settings\SetSetting;
use LaraCeemes\Actions\Taxonomies\AttachTerms;
use LaraCeemes\Actions\Taxonomies\CreateTaxonomy;
use LaraCeemes\Actions\Taxonomies\CreateTerm;
use LaraCeemes\Events\SettingUpdated;
use LaraCeemes\Facades\Navigation as NavigationFacade;
use LaraCeemes\Facades\Seo;
use LaraCeemes\Facades\Settings;
use LaraCeemes\Facades\Taxonomy as TaxonomyFacade;
use LaraCeemes\Models\Entry;
use LaraCeemes\Tests\TestCase;

final class SupportingContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_taxonomy_terms_are_hierarchical_and_available_through_facade(): void
    {
        $taxonomy = $this->app->make(CreateTaxonomy::class)->execute(['name' => 'Categories']);
        $technology = $this->app->make(CreateTerm::class)->execute($taxonomy, ['name' => 'Technology']);
        $laravel = $this->app->make(CreateTerm::class)->execute($taxonomy, [
            'name' => 'Laravel',
            'parent_uuid' => $technology->uuid,
        ]);

        self::assertSame('categories', TaxonomyFacade::get('categories')->handle);
        self::assertCount(2, TaxonomyFacade::terms('categories'));
        self::assertSame($technology->uuid, $laravel->parent->uuid);
        self::assertSame($laravel->uuid, TaxonomyFacade::term('categories', 'laravel')?->uuid);
    }

    public function test_taxonomy_relations_are_scoped_by_field_handle(): void
    {
        $entry = $this->makeEntry();
        $taxonomy = $this->app->make(CreateTaxonomy::class)->execute(['name' => 'Categories']);
        $news = $this->app->make(CreateTerm::class)->execute($taxonomy, ['name' => 'News']);
        $featured = $this->app->make(CreateTerm::class)->execute($taxonomy, ['name' => 'Featured']);
        $createField = $this->app->make(CreateBlueprintField::class);

        foreach (['categories', 'secondary_categories'] as $handle) {
            $createField->execute($entry->blueprint, [
                'handle' => $handle,
                'label' => $handle,
                'type' => 'taxonomy',
                'config' => ['taxonomy' => 'categories'],
            ]);
        }

        $attach = $this->app->make(AttachTerms::class);
        $attach->execute($entry, 'categories', [$news->uuid]);
        $attach->execute($entry, 'secondary_categories', [$featured->uuid]);
        $attach->execute($entry, 'categories', [$featured->uuid]);

        self::assertSame([$featured->uuid], $entry->terms('categories')->pluck('uuid')->all());
        self::assertSame([$featured->uuid], $entry->terms('secondary_categories')->pluck('uuid')->all());
    }

    public function test_navigation_supports_nested_items_and_reordering(): void
    {
        $navigation = $this->app->make(CreateNavigation::class)->execute(['name' => 'Header']);
        $createItem = $this->app->make(CreateNavigationItem::class);
        $home = $createItem->execute($navigation, [
            'label' => 'Home',
            'type' => 'url',
            'target' => 'https://example.com',
            'sort_order' => 0,
        ]);
        $services = $createItem->execute($navigation, [
            'label' => 'Services',
            'type' => 'route',
            'target' => 'services.index',
            'sort_order' => 1,
        ]);
        $printing = $createItem->execute($navigation, [
            'parent_uuid' => $services->uuid,
            'label' => 'Printing',
            'type' => 'route',
            'target' => 'services.printing',
        ]);

        $this->app->make(ReorderNavigationItems::class)->execute($navigation, [
            $services->uuid,
            $home->uuid,
        ]);

        $items = NavigationFacade::get('header')->items();
        self::assertSame([$services->uuid, $home->uuid], $items->pluck('uuid')->all());
        self::assertSame($printing->uuid, $items->first()?->childrenRecursive->first()?->uuid);
    }

    public function test_settings_are_typed_cached_and_invalidated_on_write(): void
    {
        Event::fake([SettingUpdated::class]);

        Settings::set('general.site_name', 'Company');
        Settings::set('cache.ttl', 1800);

        self::assertSame('Company', Settings::get('general.site_name'));
        self::assertSame(1800, Settings::get('cache.ttl'));
        self::assertTrue(Settings::has('general.site_name'));
        self::assertSame('fallback', Settings::get('missing.value', 'fallback'));

        $this->app->make(SetSetting::class)->execute('general.site_name', 'Updated Company');
        self::assertSame('Updated Company', Settings::get('general.site_name'));
        Event::assertDispatched(SettingUpdated::class);
    }

    public function test_entry_seo_overrides_global_seo_and_missing_values_fall_back(): void
    {
        Settings::set('seo.site_title', 'Company');
        Settings::set('seo.default_meta_title', 'Default Title');
        Settings::set('seo.default_meta_description', 'Default Description');
        Settings::set('seo.title_separator', '-');
        Settings::set('seo.default_robots_index', true);

        $entry = $this->makeEntry([
            'seo' => [
                'title' => 'Home SEO',
                'robots_index' => false,
            ],
        ]);
        $seo = Seo::forEntry($entry);

        self::assertSame('Home SEO', $seo->title);
        self::assertSame('Default Description', $seo->description);
        self::assertFalse($seo->robotsIndex);
        self::assertSame('Company', $seo->siteTitle);
        self::assertSame('-', $seo->titleSeparator);
    }

    /** @param array<string, mixed> $entryData */
    private function makeEntry(array $entryData = []): Entry
    {
        $collection = $this->app->make(CreateCollection::class)->execute(['name' => 'Pages']);
        $blueprint = $this->app->make(CreateBlueprint::class)->execute($collection, ['name' => 'Page']);

        return $this->app->make(CreateEntry::class)->execute($blueprint, [
            'title' => 'Home',
            ...$entryData,
        ]);
    }
}
