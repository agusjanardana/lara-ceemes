<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_sections', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('section_type_uuid')->constrained('ceemes_section_types', 'uuid')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('name');
            $table->string('handle')->unique();
            $table->json('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_sections');
    }
};
