<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_sites', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('name');
            $table->string('handle', 32)->unique();
            $table->string('locale', 20);
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['is_enabled', 'sort_order']);
        });

        $locale = (string) config('ceemes.multisite.default_locale', config('app.locale', 'en'));
        $handle = Str::slug((string) config('ceemes.multisite.default_handle', $locale)) ?: 'default';
        $now = now();

        DB::table('ceemes_sites')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => (string) config('ceemes.multisite.default_name', config('app.name', 'Default Site')),
            'handle' => $handle,
            'locale' => $locale,
            'is_default' => true,
            'is_enabled' => true,
            'sort_order' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_sites');
    }
};
