<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_content_category', function (Blueprint $table): void {
            $table->foreignUuid('content_uuid')->constrained('ceemes_contents', 'uuid')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignUuid('category_uuid')->constrained('ceemes_categories', 'uuid')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('field_handle');
            $table->primary(['content_uuid', 'category_uuid', 'field_handle'], 'ceemes_content_category_primary');
            $table->index(['content_uuid', 'field_handle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_content_category');
    }
};
