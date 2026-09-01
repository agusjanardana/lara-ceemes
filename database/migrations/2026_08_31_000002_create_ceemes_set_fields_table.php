<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_set_fields', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('set_uuid')->constrained('ceemes_sets', 'uuid')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('handle');
            $table->string('label');
            $table->string('type');
            $table->json('config')->nullable();
            $table->unsignedTinyInteger('width')->default(100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['set_uuid', 'handle']);
            $table->index(['set_uuid', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_set_fields');
    }
};
