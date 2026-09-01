<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_section_fields', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('section_type_uuid')->constrained('ceemes_section_types', 'uuid')->cascadeOnDelete()->cascadeOnUpdate();
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
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_section_fields');
    }
};
