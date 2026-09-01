<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_navigation_items', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('navigation_uuid')->constrained('ceemes_navigations', 'uuid')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignUuid('parent_uuid')->nullable()->constrained('ceemes_navigation_items', 'uuid')->nullOnDelete()->cascadeOnUpdate();
            $table->string('label');
            $table->string('type');
            $table->string('target');
            $table->json('data')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['navigation_uuid', 'parent_uuid', 'sort_order'], 'ceemes_navigation_tree_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_navigation_items');
    }
};
