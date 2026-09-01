<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_sets', function (Blueprint $table): void {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_sets');
    }
};
