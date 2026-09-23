<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use LaraCeemes\Actions\Categories\CreateCategoryGroup;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Actions\Navigations\CreateNavigation;
use LaraCeemes\Actions\Navigations\CreateNavigationItem;
use LaraCeemes\Actions\Sections\CreateSectionType;
use LaraCeemes\Actions\Sets\CreateSet;
use LaraCeemes\Actions\Sets\CreateSetField;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\MediaFolder;
use LaraCeemes\Models\Section;
use LaraCeemes\Models\Set;
use LaraCeemes\Models\SetField;
use LaraCeemes\Models\Setting;
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
            'role' => 'superadmin',
        ]);
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

    public function test_admin_set_and_field_forms_use_actions(): void
    {
        $this->actingAs($this->user)->post('/admin/sets', [
            'name' => 'Pages',
            'handle' => 'pages',
            'is_publishable' => '1',
        ])->assertRedirect();

        $set = Set::query()->sole();
        $this->actingAs($this->user)->post("/admin/sets/{$set->uuid}/fields", [
            'label' => 'Headline',
            'handle' => 'headline',
            'type' => 'text',
            'config' => '{"required":true}',
            'width' => 100,
            'sort_order' => 0,
        ])->assertRedirect();

        self::assertSame('pages', $set->handle);
        self::assertTrue(SetField::query()->sole()->config['required']);

        $this->actingAs($this->user)
            ->get('/admin/sets')
            ->assertOk()
            ->assertSee('data-resource-list', false)
            ->assertSee('<table', false)
            ->assertSee('Tambah baru');
    }

    public function test_set_flow_accepts_friendly_field_config(): void
    {
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);

        $this->actingAs($this->user)
            ->get("/admin/sets/{$set->uuid}/contents/create")
            ->assertOk()
            ->assertSee('Create Content');

        $this->actingAs($this->user)
            ->get("/admin/sets/{$set->uuid}/fields")
            ->assertNotFound();

        $this->actingAs($this->user)
            ->post("/admin/sets/{$set->uuid}/fields", [
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

        $field = SetField::query()->sole();
        self::assertSame('page_type', $field->handle);
        self::assertTrue($field->config['required']);
        self::assertSame('Landing Page', $field->config['options']['landing']);
    }

    public function test_fixed_field_can_be_added_from_content_editor_without_sidebar_shortcut(): void
    {
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);
        $content = app(CreateContent::class)->execute($set, ['title' => 'Homepage']);

        $this->actingAs($this->user)
            ->get("/admin/contents/{$content->uuid}/edit")
            ->assertOk()
            ->assertSee('+ Tambah Field')
            ->assertSee('Tambah Fixed Field pertama')
            ->assertDontSee('>FLD<', false);

        $this->actingAs($this->user)
            ->post("/admin/sets/{$set->uuid}/fields", [
                'label' => 'Summary',
                'handle' => 'summary',
                'type' => 'textarea',
                'width' => 100,
                'sort_order' => 0,
                'config' => ['required' => '0'],
                'advanced_config' => '{}',
                'redirect_content_uuid' => $content->uuid,
            ])
            ->assertRedirect("/admin/contents/{$content->uuid}/edit");

        self::assertSame('summary', $set->fields()->sole()->handle);
    }

    public function test_field_management_and_relation_pickers_stay_inside_clean_editors(): void
    {
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);
        $mediaField = app(CreateSetField::class)->execute($set, [
            'label' => 'Gallery',
            'type' => 'media',
            'config' => ['multiple' => true],
        ]);
        $content = app(CreateContent::class)->execute($set, ['title' => 'Homepage']);

        $this->actingAs($this->user)
            ->get("/admin/contents/{$content->uuid}/edit")
            ->assertOk()
            ->assertDontSee('Kelola Fields')
            ->assertSee('edit-fixed-field-'.$mediaField->uuid)
            ->assertSee('Atur struktur Gallery')
            ->assertSee('data-relation-picker', false)
            ->assertSee('Media Library')
            ->assertDontSee('<select id="content-gallery"', false);

        $sectionType = app(CreateSectionType::class)->execute(['name' => 'Hero']);

        $this->actingAs($this->user)
            ->get("/admin/section-types/{$sectionType->uuid}/fields")
            ->assertOk()
            ->assertSee('data-field-type-picker', false)
            ->assertSee('Pilih Field Type')
            ->assertSee('File dari Media Library');
    }

    public function test_sidebar_groups_and_media_folder_filters_are_available(): void
    {
        app(CreateSet::class)->execute(['name' => 'Products']);
        app(CreateCategoryGroup::class)->execute(['name' => 'Product Categories']);

        $this->actingAs($this->user)
            ->get('/admin/media')
            ->assertOk()
            ->assertSee('data-nav-group="sets"', false)
            ->assertSee('data-nav-group="categories"', false)
            ->assertSee('Products')
            ->assertSee('Product Categories')
            ->assertSee('Semua jenis')
            ->assertSee('+ Folder');

        $this->actingAs($this->user)
            ->post('/admin/media-folders', ['name' => 'Product Photos'])
            ->assertRedirect();

        self::assertSame('product-photos', MediaFolder::query()->sole()->path);
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

    public function test_content_editor_accepts_human_friendly_set_fields(): void
    {
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);
        app(CreateSetField::class)->execute($set, [
            'label' => 'Headline',
            'handle' => 'headline',
            'type' => 'text',
            'config' => ['required' => true],
        ]);

        $this->actingAs($this->user)->post("/admin/sets/{$set->uuid}/contents", [
            'title' => 'Home',
            'slug' => 'home',
            'status' => 'published',
            'data' => ['headline' => 'Build better websites'],
            'seo' => ['title' => 'Home page'],
        ])->assertRedirect();

        $content = Content::query()->sole();
        self::assertSame('Build better websites', $content->get('headline'));

        $this->actingAs($this->user)
            ->get("/admin/contents/{$content->uuid}/edit")
            ->assertOk()
            ->assertSee('Headline')
            ->assertSee('Build better websites')
            ->assertSee('Sections area');
    }

    public function test_field_builder_stores_validation_and_visibility_configuration(): void
    {
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);
        app(CreateSetField::class)->execute($set, [
            'label' => 'Layout',
            'handle' => 'layout',
            'type' => 'select',
            'config' => ['options' => ['default' => 'Default', 'campaign' => 'Campaign']],
        ]);

        $this->actingAs($this->user)->post("/admin/sets/{$set->uuid}/fields", [
            'label' => 'Campaign Headline',
            'handle' => 'campaign_headline',
            'type' => 'text',
            'width' => 100,
            'sort_order' => 1,
            'config' => [
                'required' => '1',
                'min_length' => '10',
                'max_length' => '80',
                'validation_message' => 'Campaign headline harus berisi 10-80 karakter.',
                'visibility' => ['enabled' => '1', 'field' => 'layout', 'operator' => 'equals', 'value' => 'campaign'],
            ],
            'advanced_config' => '{}',
        ])->assertRedirect();

        $field = SetField::query()->where('handle', 'campaign_headline')->sole();
        self::assertTrue($field->config['required']);
        self::assertSame(10, $field->config['min_length']);
        self::assertSame(80, $field->config['max_length']);
        self::assertSame('layout', $field->config['visibility']['field']);
        self::assertSame('campaign', $field->config['visibility']['value']);

        $content = app(CreateContent::class)->execute($set, ['title' => 'Home', 'data' => ['layout' => 'default']]);
        $this->actingAs($this->user)->get("/admin/contents/{$content->uuid}/edit")
            ->assertOk()
            ->assertSee('Validation')
            ->assertSee('Visibility')
            ->assertSee('data-conditional-field', false)
            ->assertSee('data-visibility-source="layout"', false);
    }

    public function test_set_sections_field_controls_the_content_page_builder(): void
    {
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);
        $hero = app(CreateSectionType::class)->execute(['name' => 'Hero']);
        $cta = app(CreateSectionType::class)->execute(['name' => 'CTA']);

        $this->actingAs($this->user)
            ->post("/admin/sets/{$set->uuid}/fields", [
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

        $content = app(CreateContent::class)->execute($set, ['title' => 'Home']);

        $this->actingAs($this->user)
            ->get("/admin/contents/{$content->uuid}/edit")
            ->assertOk()
            ->assertSee('Sections area')
            ->assertSee('Page Builder')
            ->assertSee('Hero')
            ->assertDontSee('value="'.$cta->uuid.'"', false);

        $field = SetField::query()->where('handle', 'page_builder')->sole();
        self::assertSame(['hero'], $field->config['allowed']);

        $this->actingAs($this->user)
            ->post("/admin/contents/{$content->uuid}/sections", [
                'section_type_uuid' => $hero->uuid,
                'field_handle' => 'page_builder',
                'section_data' => [$hero->uuid => []],
            ])
            ->assertRedirect();

        self::assertSame('page_builder', Section::query()->sole()->contents()->sole()->pivot->region);
    }

    public function test_content_field_picker_is_set_scoped_and_supports_multiple_values(): void
    {
        $products = app(CreateSet::class)->execute(['name' => 'Products']);
        $productA = app(CreateContent::class)->execute($products, ['title' => 'Product A']);
        $productB = app(CreateContent::class)->execute($products, ['title' => 'Product B']);

        $pages = app(CreateSet::class)->execute(['name' => 'Pages']);
        app(CreateSetField::class)->execute($pages, [
            'label' => 'Featured Products',
            'type' => 'content',
            'config' => [
                'set' => 'products',
                'multiple' => true,
                'required' => true,
            ],
        ]);
        $home = app(CreateContent::class)->execute($pages, [
            'title' => 'Home',
            'data' => ['featured_products' => [$productA->uuid, $productB->uuid]],
        ]);

        self::assertSame([$productA->uuid, $productB->uuid], $home->get('featured_products'));

        $this->actingAs($this->user)
            ->get("/admin/contents/{$home->uuid}/edit")
            ->assertOk()
            ->assertSee('data-content-picker', false)
            ->assertSee('data-field-type-picker', false)
            ->assertSee('Pilih Field Type')
            ->assertSee('Relasi ke Content dari Set')
            ->assertSee('Pilih Set')
            ->assertSee('Product A')
            ->assertSee('Product B');

        $this->actingAs($this->user)
            ->post("/admin/sets/{$pages->uuid}/fields", [
                'label' => 'Related Products',
                'type' => 'content',
                'width' => 100,
                'sort_order' => 1,
                'config' => [
                    'set' => 'products',
                    'required' => '0',
                    'multiple' => '1',
                ],
                'advanced_config' => '{}',
            ])
            ->assertRedirect();

        $related = SetField::query()->where('handle', 'related_products')->sole();
        self::assertSame('products', $related->config['set']);
        self::assertTrue($related->config['multiple']);
    }

    public function test_repeater_set_builder_and_content_editor_use_repeatable_rows(): void
    {
        $set = app(CreateSet::class)->execute(['name' => 'Pages']);
        $this->actingAs($this->user)
            ->post("/admin/sets/{$set->uuid}/fields", [
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

        $field = SetField::query()->sole();
        self::assertSame('title', $field->config['fields'][0]['handle']);
        self::assertTrue($field->config['fields'][0]['config']['required']);
        self::assertSame(3, $field->config['max_rows']);

        $content = app(CreateContent::class)->execute($set, [
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
        ], $content->get('features'));

        $this->actingAs($this->user)
            ->get("/admin/contents/{$content->uuid}/edit")
            ->assertOk()
            ->assertSee('data-repeater', false)
            ->assertSee('Tambah item')
            ->assertSee('Apa yang akan diulang?')
            ->assertSee('Tambah subfield')
            ->assertSee('Butuh blok berbeda? Gunakan Sections')
            ->assertSee('Quick response')
            ->assertDontSee('Structured field. Gunakan JSON');
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
