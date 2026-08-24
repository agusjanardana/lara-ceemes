<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_collections', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->string('handle')->unique();
            $table->text('description')->nullable();
            $table->string('route')->nullable();
            $table->string('template')->nullable();
            $table->boolean('is_publishable')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('ceemes_blueprints', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('collection_uuid')
                ->constrained('ceemes_collections', 'uuid')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('name');
            $table->string('handle');
            $table->timestamps();

            $table->unique(['collection_uuid', 'handle']);
        });

        Schema::create('ceemes_blueprint_fields', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('blueprint_uuid')
                ->constrained('ceemes_blueprints', 'uuid')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('handle');
            $table->string('label');
            $table->string('type');
            $table->json('config')->nullable();
            $table->unsignedTinyInteger('width')->default(100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['blueprint_uuid', 'handle']);
            $table->index(['blueprint_uuid', 'sort_order']);
        });

        Schema::create('ceemes_entries', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('collection_uuid')
                ->constrained('ceemes_collections', 'uuid')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignUuid('blueprint_uuid')
                ->constrained('ceemes_blueprints', 'uuid')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('title');
            $table->string('slug');
            $table->string('uri')->nullable()->index();
            $table->json('data');
            $table->json('seo')->nullable();
            $table->string('status')->default('draft');
            $this->addUserReference($table, 'created_by');
            $this->addUserReference($table, 'updated_by');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['collection_uuid', 'slug']);
            $table->index(['collection_uuid', 'status']);
            $table->index(['blueprint_uuid', 'status']);
        });

        Schema::create('ceemes_section_types', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->string('handle')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('ceemes_section_fields', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('section_type_uuid')
                ->constrained('ceemes_section_types', 'uuid')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('handle');
            $table->string('label');
            $table->string('type');
            $table->json('config')->nullable();
            $table->unsignedTinyInteger('width')->default(100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['section_type_uuid', 'handle']);
            $table->index(['section_type_uuid', 'sort_order']);
        });

        Schema::create('ceemes_sections', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('entry_uuid')
                ->constrained('ceemes_entries', 'uuid')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignUuid('section_type_uuid')
                ->constrained('ceemes_section_types', 'uuid')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->string('field_handle')->default('sections');
            $table->string('key')->nullable();
            $table->json('data');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['entry_uuid', 'field_handle', 'key']);
            $table->index(['entry_uuid', 'field_handle', 'is_enabled', 'sort_order'], 'ceemes_sections_lookup_index');
        });

        Schema::create('ceemes_taxonomies', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->string('handle')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('ceemes_terms', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('taxonomy_uuid')
                ->constrained('ceemes_taxonomies', 'uuid')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignUuid('parent_uuid')
                ->nullable()
                ->constrained('ceemes_terms', 'uuid')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('name');
            $table->string('slug');
            $table->json('data')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['taxonomy_uuid', 'slug']);
            $table->index(['taxonomy_uuid', 'parent_uuid', 'sort_order'], 'ceemes_terms_tree_index');
        });

        Schema::create('ceemes_entry_term', function (Blueprint $table): void {
            $table->foreignUuid('entry_uuid')
                ->constrained('ceemes_entries', 'uuid')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignUuid('term_uuid')
                ->constrained('ceemes_terms', 'uuid')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('field_handle');

            $table->primary(['entry_uuid', 'term_uuid', 'field_handle'], 'ceemes_entry_term_primary');
            $table->index(['entry_uuid', 'field_handle']);
        });

        Schema::create('ceemes_navigations', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->string('handle')->unique();
            $table->timestamps();
        });

        Schema::create('ceemes_navigation_items', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('navigation_uuid')
                ->constrained('ceemes_navigations', 'uuid')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignUuid('parent_uuid')
                ->nullable()
                ->constrained('ceemes_navigation_items', 'uuid')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->string('label');
            $table->string('type');
            $table->string('target');
            $table->json('data')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['navigation_uuid', 'parent_uuid', 'sort_order'], 'ceemes_navigation_tree_index');
        });

        Schema::create('ceemes_settings', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('group');
            $table->string('key');
            $table->json('value')->nullable();
            $table->string('type')->default('string');
            $table->boolean('autoload')->default(false);
            $table->timestamps();

            $table->unique(['group', 'key']);
            $table->index(['autoload', 'group']);
        });

        Schema::create('ceemes_media', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('disk');
            $table->string('directory')->default('');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('extension', 32);
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('title')->nullable();
            $table->text('alt')->nullable();
            $table->text('caption')->nullable();
            $this->addUserReference($table, 'uploaded_by');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['disk', 'directory', 'filename'], 'ceemes_media_path_unique');
            $table->index(['mime_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_media');
        Schema::dropIfExists('ceemes_settings');
        Schema::dropIfExists('ceemes_navigation_items');
        Schema::dropIfExists('ceemes_navigations');
        Schema::dropIfExists('ceemes_entry_term');
        Schema::dropIfExists('ceemes_terms');
        Schema::dropIfExists('ceemes_taxonomies');
        Schema::dropIfExists('ceemes_sections');
        Schema::dropIfExists('ceemes_section_fields');
        Schema::dropIfExists('ceemes_section_types');
        Schema::dropIfExists('ceemes_entries');
        Schema::dropIfExists('ceemes_blueprint_fields');
        Schema::dropIfExists('ceemes_blueprints');
        Schema::dropIfExists('ceemes_collections');
    }

    private function addUserReference(Blueprint $table, string $column): void
    {
        match (config('ceemes.users.key_type', 'integer')) {
            'uuid' => $table->uuid($column)->nullable(),
            'ulid' => $table->ulid($column)->nullable(),
            'integer' => $table->unsignedBigInteger($column)->nullable(),
            default => throw new InvalidArgumentException('Unsupported Ceemes user key type.'),
        };

        if (! config('ceemes.users.foreign_keys', true)) {
            return;
        }

        $table->foreign($column)
            ->references((string) config('ceemes.users.key', 'id'))
            ->on((string) config('ceemes.users.table', 'users'))
            ->nullOnDelete()
            ->cascadeOnUpdate();
    }
};
