<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use LaraCeemes\Actions\Categories\AttachCategories;
use LaraCeemes\Actions\Categories\CreateCategory;
use LaraCeemes\Actions\Categories\CreateCategoryGroup;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Actions\Navigations\CreateNavigation;
use LaraCeemes\Actions\Navigations\CreateNavigationItem;
use LaraCeemes\Actions\Navigations\ReorderNavigationItems;
use LaraCeemes\Actions\Sets\CreateSet;
use LaraCeemes\Actions\Sets\CreateSetField;
use LaraCeemes\Actions\Settings\SetSetting;
use LaraCeemes\Events\SettingUpdated;
use LaraCeemes\Facades\Category as CategoryFacade;
use LaraCeemes\Facades\Navigation as NavigationFacade;
use LaraCeemes\Facades\Seo;
use LaraCeemes\Facades\Settings;
use LaraCeemes\Models\Content;
use LaraCeemes\Tests\TestCase;

final class SupportingContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_are_hierarchical_and_available_through_facade(): void
    {
        $categoryGroup = $this->app->make(CreateCategoryGroup::class)->execute(['name' => 'Categories']);
        $technology = $this->app->make(CreateCategory::class)->execute($categoryGroup, ['name' => 'Technology']);
        $laravel = $this->app->make(CreateCategory::class)->execute($categoryGroup, [
            'name' => 'Laravel',
            'parent_uuid' => $technology->uuid,
        ]);

        self::assertSame('categories', CategoryFacade::get('categories')->handle);
        self::assertCount(2, CategoryFacade::categories('categories'));
        self::assertSame($technology->uuid, $laravel->parent->uuid);
        self::assertSame($laravel->uuid, CategoryFacade::category('categories', 'laravel')?->uuid);
    }

    public function test_category_relations_are_scoped_by_field_handle(): void
    {
        $content = $this->makeContent();
        $categoryGroup = $this->app->make(CreateCategoryGroup::class)->execute(['name' => 'Categories']);
        $news = $this->app->make(CreateCategory::class)->execute($categoryGroup, ['name' => 'News']);
        $featured = $this->app->make(CreateCategory::class)->execute($categoryGroup, ['name' => 'Featured']);
        $createField = $this->app->make(CreateSetField::class);

        foreach (['categories', 'secondary_categories'] as $handle) {
            $createField->execute($content->set, [
                'handle' => $handle,
                'label' => $handle,
                'type' => 'category',
                'config' => ['category_group' => 'categories'],
            ]);
        }

        $attach = $this->app->make(AttachCategories::class);
        $attach->execute($content, 'categories', [$news->uuid]);
        $attach->execute($content, 'secondary_categories', [$featured->uuid]);
        $attach->execute($content, 'categories', [$featured->uuid]);

        self::assertSame([$featured->uuid], $content->categories('categories')->pluck('uuid')->all());
        self::assertSame([$featured->uuid], $content->categories('secondary_categories')->pluck('uuid')->all());
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

    public function test_content_seo_overrides_global_seo_and_missing_values_fall_back(): void
    {
        Settings::set('seo.site_title', 'Company');
        Settings::set('seo.default_meta_title', 'Default Title');
        Settings::set('seo.default_meta_description', 'Default Description');
        Settings::set('seo.title_separator', '-');
        Settings::set('seo.default_robots_index', true);

        $content = $this->makeContent([
            'seo' => [
                'title' => 'Home SEO',
                'robots_index' => false,
            ],
        ]);
        $seo = Seo::forContent($content);

        self::assertSame('Home SEO', $seo->title);
        self::assertSame('Default Description', $seo->description);
        self::assertFalse($seo->robotsIndex);
        self::assertSame('Company', $seo->siteTitle);
        self::assertSame('-', $seo->titleSeparator);
    }

    /** @param array<string, mixed> $contentData */
    private function makeContent(array $contentData = []): Content
    {
        $set = $this->app->make(CreateSet::class)->execute(['name' => 'Pages']);

        return $this->app->make(CreateContent::class)->execute($set, [
            'title' => 'Home',
            ...$contentData,
        ]);
    }
}
