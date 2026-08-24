<?php

declare(strict_types=1);

namespace LaraCeemes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use LaraCeemes\Tests\TestCase;

final class FoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_package_configuration_is_registered(): void
    {
        self::assertSame('admin', config('ceemes.admin.prefix'));
        self::assertSame('public', config('ceemes.media.disk'));
        self::assertSame('integer', config('ceemes.users.key_type'));
    }

    public function test_all_ceemes_tables_are_migrated(): void
    {
        $tables = [
            'ceemes_collections',
            'ceemes_blueprints',
            'ceemes_blueprint_fields',
            'ceemes_entries',
            'ceemes_section_types',
            'ceemes_section_fields',
            'ceemes_sections',
            'ceemes_taxonomies',
            'ceemes_terms',
            'ceemes_entry_term',
            'ceemes_navigations',
            'ceemes_navigation_items',
            'ceemes_settings',
            'ceemes_media',
            'ceemes_super_admins',
        ];

        foreach ($tables as $table) {
            self::assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_cms_tables_use_uuid_primary_keys_without_integer_ids(): void
    {
        self::assertTrue(Schema::hasColumn('ceemes_collections', 'uuid'));
        self::assertFalse(Schema::hasColumn('ceemes_collections', 'id'));
        self::assertTrue(Schema::hasColumn('ceemes_entries', 'uuid'));
        self::assertFalse(Schema::hasColumn('ceemes_entries', 'id'));
        self::assertTrue(Schema::hasColumn('ceemes_media', 'uuid'));
        self::assertFalse(Schema::hasColumn('ceemes_media', 'id'));
    }

    public function test_deleting_a_user_nulls_content_actor_references(): void
    {
        DB::table('users')->insert([
            'id' => 1,
            'name' => 'Editor',
            'email' => 'editor@example.com',
            'password' => 'unused-test-password',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $collectionUuid = (string) Str::uuid();
        $blueprintUuid = (string) Str::uuid();
        $entryUuid = (string) Str::uuid();

        DB::table('ceemes_collections')->insert([
            'uuid' => $collectionUuid,
            'name' => 'Pages',
            'handle' => 'pages',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ceemes_blueprints')->insert([
            'uuid' => $blueprintUuid,
            'collection_uuid' => $collectionUuid,
            'name' => 'Page',
            'handle' => 'page',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ceemes_entries')->insert([
            'uuid' => $entryUuid,
            'collection_uuid' => $collectionUuid,
            'blueprint_uuid' => $blueprintUuid,
            'title' => 'Home',
            'slug' => 'home',
            'data' => '{}',
            'status' => 'draft',
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->where('id', 1)->delete();

        self::assertNull(
            DB::table('ceemes_entries')->where('uuid', $entryUuid)->value('created_by'),
        );
    }
}
