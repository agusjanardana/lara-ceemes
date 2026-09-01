<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Actions\Sets\CreateSet;
use LaraCeemes\Enums\ContentStatus;
use LaraCeemes\Support\SetViewScaffolder;
use LaraCeemes\Tests\TestCase;

final class SetViewScaffoldingTest extends TestCase
{
    use RefreshDatabase;

    public function test_automatic_templates_follow_pages_and_other_set_conventions(): void
    {
        $files = app(Filesystem::class);
        $temporaryResourcePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lara-ceemes-'.Str::uuid();
        config()->set('ceemes.views.auto_scaffold', true);
        config()->set('ceemes.views.path', $temporaryResourcePath.'/views');

        try {
            $pages = app(CreateSet::class)->execute(['name' => 'Pages']);
            $products = app(CreateSet::class)->execute(['name' => 'Products']);

            self::assertSame('/{slug}', $pages->route);
            self::assertSame('pages', $pages->template);
            self::assertFileExists($temporaryResourcePath.'/views/pages.blade.php');

            self::assertSame('/products/{slug}', $products->route);
            self::assertSame('products.show', $products->template);
            self::assertFileExists($temporaryResourcePath.'/views/products/show.blade.php');
            self::assertStringContainsString('$content->title', $files->get($temporaryResourcePath.'/views/products/show.blade.php'));

            view()->addLocation($temporaryResourcePath.'/views');
            $product = app(CreateContent::class)->execute($products, [
                'title' => 'Red Shoes',
                'status' => ContentStatus::Published->value,
            ]);
            self::assertSame('/products/red-shoes', $product->uri);
            $this->get('/products/red-shoes')
                ->assertOk()
                ->assertSee('data-ceemes-set="products"', false)
                ->assertSee('Red Shoes');

            $files->put($temporaryResourcePath.'/views/products/show.blade.php', 'developer customization');
            app(SetViewScaffolder::class)->scaffold($products);
            self::assertSame('developer customization', $files->get($temporaryResourcePath.'/views/products/show.blade.php'));
        } finally {
            $files->deleteDirectory($temporaryResourcePath);
        }
    }

    public function test_data_only_set_has_no_route_template_or_generated_file(): void
    {
        $files = app(Filesystem::class);
        $temporaryResourcePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lara-ceemes-'.Str::uuid();
        config()->set('ceemes.views.auto_scaffold', true);
        config()->set('ceemes.views.path', $temporaryResourcePath.'/views');

        try {
            $set = app(CreateSet::class)->execute([
                'name' => 'Inventory',
                'template_mode' => 'none',
            ]);

            self::assertNull($set->route);
            self::assertNull($set->template);
            self::assertFalse($set->is_publishable);
            self::assertFileDoesNotExist($temporaryResourcePath.'/views/inventory/show.blade.php');
        } finally {
            $files->deleteDirectory($temporaryResourcePath);
        }
    }

    public function test_custom_template_keeps_custom_view_and_uses_default_route_convention(): void
    {
        $set = app(CreateSet::class)->execute([
            'name' => 'Products',
            'template_mode' => 'custom',
            'template' => 'store.product-detail',
        ]);

        self::assertSame('store.product-detail', $set->template);
        self::assertSame('/products/{slug}', $set->route);
        self::assertTrue($set->is_publishable);
    }
}
