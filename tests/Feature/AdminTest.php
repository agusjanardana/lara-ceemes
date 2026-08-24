<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use LaraCeemes\Actions\Blueprints\CreateBlueprint;
use LaraCeemes\Actions\Blueprints\CreateBlueprintField;
use LaraCeemes\Actions\Collections\CreateCollection;
use LaraCeemes\Actions\Entries\CreateEntry;
use LaraCeemes\Actions\Navigations\CreateNavigation;
use LaraCeemes\Actions\Navigations\CreateNavigationItem;
use LaraCeemes\Actions\Sections\CreateSectionType;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Models\BlueprintField;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\Section;
use LaraCeemes\Models\Setting;
use LaraCeemes\Support\SuperAdminRegistry;
use LaraCeemes\Tests\Fixtures\User;
use LaraCeemes\Tests\TestCase;

final class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::query()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);
        app(SuperAdminRegistry::class)->promote($this->user);
    }

    public function test_guest_is_redirected_to_package_login_page(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Selamat datang.');
    }

    public function test_authenticated_authorized_user_can_open_admin_dashboard(): void
    {
        $this->actingAs($this->user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Lara Ceemes')
            ->assertSee('Dashboard');
    }

    public function test_consuming_application_can_override_admin_gate(): void
    {
        Gate::define('access-ceemes', static fn (): bool => false);

        $this->actingAs($this->user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_collection_blueprint_and_field_forms_use_actions(): void
    {
        $this->actingAs($this->user)->post('/admin/collections', [
            'name' => 'Pages',
            'handle' => 'pages',
            'is_publishable' => '1',
        ])->assertRedirect();

        $collection = Collection::query()->sole();
        $this->actingAs($this->user)->post("/admin/collections/{$collection->uuid}/blueprints", [
            'name' => 'Landing Page',
            'handle' => 'landing-page',
        ])->assertRedirect();

        $blueprint = Blueprint::query()->sole();
        $this->actingAs($this->user)->post("/admin/blueprints/{$blueprint->uuid}/fields", [
            'label' => 'Headline',
            'handle' => 'headline',
            'type' => 'text',
            'config' => '{"required":true}',
            'width' => 100,
            'sort_order' => 0,
        ])->assertRedirect();

        self::assertSame('pages', $collection->handle);
        self::assertSame('landing-page', $blueprint->handle);
        self::assertTrue(BlueprintField::query()->sole()->config['required']);

        $this->actingAs($this->user)
            ->get('/admin/collections')
            ->assertOk()
            ->assertSee('data-resource-list', false)
            ->assertSee('<table', false)
            ->assertSee('Tambah baru');
    }

    public function test_blueprint_setup_flow_is_discoverable_and_accepts_friendly_field_config(): void
    {
        $collection = app(CreateCollection::class)->execute(['name' => 'Pages']);

        $this->actingAs($this->user)
            ->get("/admin/collections/{$collection->uuid}/entries/create")
            ->assertOk()
            ->assertSee('Siapkan Blueprint dahulu')
            ->assertSee('Buat Blueprint sekarang');

        $this->actingAs($this->user)
            ->get("/admin/collections/{$collection->uuid}/blueprints")
            ->assertOk()
            ->assertSee('Buat Blueprint pertama')
            ->assertSee('Collection ini belum memiliki Blueprint');

        $this->actingAs($this->user)
            ->post("/admin/collections/{$collection->uuid}/blueprints", [
                'name' => 'Standard Page',
                'handle' => '',
            ])
            ->assertRedirect();

        $blueprint = Blueprint::query()->sole();
        self::assertSame('standard-page', $blueprint->handle);

        $this->actingAs($this->user)
            ->get("/admin/blueprints/{$blueprint->uuid}/fields")
            ->assertOk()
            ->assertSee('Blueprint Fields')
            ->assertSee('Tambah Field pertama');

        $this->actingAs($this->user)
            ->post("/admin/blueprints/{$blueprint->uuid}/fields", [
                'label' => 'Page Type',
                'handle' => '',
                'type' => 'select',
                'width' => 50,
                'sort_order' => 0,
                'config' => [
                    'required' => '1',
                    'multiple' => '0',
                    'instructions' => 'Pilih jenis halaman.',
                    'options_text' => "standard: Standard\nlanding: Landing Page",
                ],
                'advanced_config' => '{}',
            ])
            ->assertRedirect();

        $field = BlueprintField::query()->sole();
        self::assertSame('page_type', $field->handle);
        self::assertTrue($field->config['required']);
        self::assertSame('Landing Page', $field->config['options']['landing']);
    }

    public function test_admin_setting_form_writes_typed_setting(): void
    {
        $this->actingAs($this->user)->post('/admin/settings', [
            'key' => 'cache.ttl',
            'type' => 'integer',
            'value' => '900',
            'autoload' => '1',
        ])->assertRedirect();

        $setting = Setting::query()->sole();
        self::assertSame(900, $setting->value);
        self::assertTrue($setting->autoload);
    }

    public function test_entry_editor_accepts_human_friendly_blueprint_fields(): void
    {
        $collection = app(CreateCollection::class)->execute(['name' => 'Pages']);
        $blueprint = app(CreateBlueprint::class)->execute($collection, ['name' => 'Page']);
        app(CreateBlueprintField::class)->execute($blueprint, [
            'label' => 'Headline',
            'handle' => 'headline',
            'type' => 'text',
            'config' => ['required' => true],
        ]);

        $this->actingAs($this->user)->post("/admin/collections/{$collection->uuid}/entries", [
            'blueprint_uuid' => $blueprint->uuid,
            'title' => 'Home',
            'slug' => 'home',
            'status' => 'published',
            'blueprint_data' => [
                $blueprint->uuid => ['headline' => 'Build better websites'],
            ],
            'seo' => ['title' => 'Home page'],
        ])->assertRedirect();

        $entry = Entry::query()->sole();
        self::assertSame('Build better websites', $entry->get('headline'));

        $this->actingAs($this->user)
            ->get("/admin/entries/{$entry->uuid}/edit")
            ->assertOk()
            ->assertSee('Headline')
            ->assertSee('Build better websites')
            ->assertDontSee('Sections area');
    }

    public function test_blueprint_sections_field_controls_the_entry_page_builder(): void
    {
        $collection = app(CreateCollection::class)->execute(['name' => 'Pages']);
        $blueprint = app(CreateBlueprint::class)->execute($collection, ['name' => 'Landing Page']);
        $hero = app(CreateSectionType::class)->execute(['name' => 'Hero']);
        app(CreateSectionType::class)->execute(['name' => 'CTA']);

        $this->actingAs($this->user)
            ->get("/admin/blueprints/{$blueprint->uuid}/fields")
            ->assertOk()
            ->assertSee('Sections (Page Builder)')
            ->assertSee('Hero')
            ->assertSee('CTA');

        $this->actingAs($this->user)
            ->post("/admin/blueprints/{$blueprint->uuid}/fields", [
                'label' => 'Page Builder',
                'handle' => 'page_builder',
                'type' => 'sections',
                'width' => 100,
                'sort_order' => 0,
                'config' => [
                    'required' => '0',
                    'multiple' => '0',
                    'allowed' => ['hero'],
                ],
                'advanced_config' => '{}',
            ])
            ->assertRedirect();

        $entry = app(CreateEntry::class)->execute($blueprint, ['title' => 'Home']);

        $this->actingAs($this->user)
            ->get("/admin/entries/{$entry->uuid}/edit")
            ->assertOk()
            ->assertSee('Sections area')
            ->assertSee('Page Builder')
            ->assertSee('Hero')
            ->assertDontSee('>CTA<', false);

        $field = BlueprintField::query()->where('handle', 'page_builder')->sole();
        self::assertSame(['hero'], $field->config['allowed']);

        $this->actingAs($this->user)
            ->post("/admin/entries/{$entry->uuid}/sections", [
                'section_type_uuid' => $hero->uuid,
                'field_handle' => 'page_builder',
                'section_data' => [$hero->uuid => []],
            ])
            ->assertRedirect();

        self::assertSame('page_builder', Section::query()->sole()->field_handle);
    }

    public function test_entry_field_picker_is_collection_scoped_and_supports_multiple_values(): void
    {
        $products = app(CreateCollection::class)->execute(['name' => 'Products']);
        $productBlueprint = app(CreateBlueprint::class)->execute($products, ['name' => 'Product']);
        $productA = app(CreateEntry::class)->execute($productBlueprint, ['title' => 'Product A']);
        $productB = app(CreateEntry::class)->execute($productBlueprint, ['title' => 'Product B']);

        $pages = app(CreateCollection::class)->execute(['name' => 'Pages']);
        $pageBlueprint = app(CreateBlueprint::class)->execute($pages, ['name' => 'Homepage']);
        app(CreateBlueprintField::class)->execute($pageBlueprint, [
            'label' => 'Featured Products',
            'type' => 'entry',
            'config' => [
                'collection' => 'products',
                'multiple' => true,
                'required' => true,
            ],
        ]);
        $home = app(CreateEntry::class)->execute($pageBlueprint, [
            'title' => 'Home',
            'data' => ['featured_products' => [$productA->uuid, $productB->uuid]],
        ]);

        self::assertSame([$productA->uuid, $productB->uuid], $home->get('featured_products'));

        $this->actingAs($this->user)
            ->get("/admin/entries/{$home->uuid}/edit")
            ->assertOk()
            ->assertSee('data-entry-picker', false)
            ->assertSee('Pilih Collection')
            ->assertSee('Product A')
            ->assertSee('Product B');

        $this->actingAs($this->user)
            ->get("/admin/blueprints/{$pageBlueprint->uuid}/fields")
            ->assertOk()
            ->assertSee('data-field-type-picker', false)
            ->assertSee('Pilih Field Type')
            ->assertSee('Relasi ke Entry dari Collection');

        $this->actingAs($this->user)
            ->post("/admin/blueprints/{$pageBlueprint->uuid}/fields", [
                'label' => 'Related Products',
                'type' => 'entry',
                'width' => 100,
                'sort_order' => 1,
                'config' => [
                    'collection' => 'products',
                    'required' => '0',
                    'multiple' => '1',
                ],
                'advanced_config' => '{}',
            ])
            ->assertRedirect();

        $related = BlueprintField::query()->where('handle', 'related_products')->sole();
        self::assertSame('products', $related->config['collection']);
        self::assertTrue($related->config['multiple']);
    }

    public function test_repeater_blueprint_builder_and_entry_editor_use_repeatable_rows(): void
    {
        $collection = app(CreateCollection::class)->execute(['name' => 'Pages']);
        $blueprint = app(CreateBlueprint::class)->execute($collection, ['name' => 'Homepage']);

        $this->actingAs($this->user)
            ->post("/admin/blueprints/{$blueprint->uuid}/fields", [
                'label' => 'Features',
                'type' => 'repeater',
                'width' => 100,
                'sort_order' => 0,
                'config' => [
                    'required' => '0',
                    'min_rows' => '1',
                    'max_rows' => '3',
                    'fields' => [
                        ['label' => 'Title', 'handle' => '', 'type' => 'text', 'required' => '1', 'width' => '50'],
                        ['label' => 'Description', 'handle' => 'description', 'type' => 'textarea', 'required' => '0', 'width' => '50'],
                    ],
                ],
                'advanced_config' => '{}',
            ])
            ->assertRedirect();

        $field = BlueprintField::query()->sole();
        self::assertSame('title', $field->config['fields'][0]['handle']);
        self::assertTrue($field->config['fields'][0]['config']['required']);
        self::assertSame(3, $field->config['max_rows']);

        $entry = app(CreateEntry::class)->execute($blueprint, [
            'title' => 'Home',
            'data' => [
                'features' => [
                    ['title' => 'Fast', 'description' => 'Quick response', 'unknown' => 'discard me'],
                    ['title' => 'Friendly', 'description' => 'No JSON editor'],
                ],
            ],
        ]);

        self::assertSame([
            ['title' => 'Fast', 'description' => 'Quick response'],
            ['title' => 'Friendly', 'description' => 'No JSON editor'],
        ], $entry->get('features'));

        $this->actingAs($this->user)
            ->get("/admin/entries/{$entry->uuid}/edit")
            ->assertOk()
            ->assertSee('data-repeater', false)
            ->assertSee('Tambah item')
            ->assertSee('Quick response')
            ->assertDontSee('Structured field. Gunakan JSON');

        $this->actingAs($this->user)
            ->get("/admin/blueprints/{$blueprint->uuid}/fields")
            ->assertOk()
            ->assertSee('Apa yang akan diulang?')
            ->assertSee('Tambah subfield')
            ->assertSee('Butuh blok berbeda? Gunakan Sections');
    }

    public function test_navigation_items_are_presented_as_parent_child_tree(): void
    {
        $navigation = app(CreateNavigation::class)->execute(['name' => 'Header']);
        $parent = app(CreateNavigationItem::class)->execute($navigation, [
            'label' => 'Products',
            'type' => 'url',
            'target' => 'https://example.com/products',
        ]);
        app(CreateNavigationItem::class)->execute($navigation, [
            'label' => 'Shoes',
            'type' => 'url',
            'target' => 'https://example.com/products/shoes',
            'parent_uuid' => $parent->uuid,
        ]);

        $this->actingAs($this->user)
            ->get("/admin/navigations/{$navigation->uuid}/items")
            ->assertOk()
            ->assertSeeInOrder(['Products', 'Shoes'])
            ->assertSee('data-resource-list', false);
    }
}
