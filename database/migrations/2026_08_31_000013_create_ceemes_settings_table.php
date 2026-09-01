<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_settings');
    }
};
