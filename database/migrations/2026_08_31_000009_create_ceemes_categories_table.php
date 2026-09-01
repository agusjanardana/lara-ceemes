<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_categories', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('category_group_uuid')->constrained('ceemes_category_groups', 'uuid')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignUuid('parent_uuid')->nullable()->constrained('ceemes_categories', 'uuid')->nullOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->string('slug');
            $table->json('data')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['category_group_uuid', 'slug']);
            $table->index(['category_group_uuid', 'parent_uuid', 'sort_order'], 'ceemes_categories_tree_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_categories');
    }
};
