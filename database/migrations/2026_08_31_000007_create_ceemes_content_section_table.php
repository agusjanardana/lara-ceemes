<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_content_section', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('content_uuid')->constrained('ceemes_contents', 'uuid')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignUuid('section_uuid')->constrained('ceemes_sections', 'uuid')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('region')->default('sections');
            $table->string('key')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->unique(['content_uuid', 'section_uuid', 'region']);
            $table->index(['content_uuid', 'region', 'is_enabled', 'sort_order'], 'ceemes_content_section_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_content_section');
    }
};
