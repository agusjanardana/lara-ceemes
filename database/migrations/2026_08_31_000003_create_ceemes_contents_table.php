<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_contents', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->foreignUuid('set_uuid')->constrained('ceemes_sets', 'uuid')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('title');
            $table->string('slug');
            $table->string('uri')->unique();
            $table->json('data');
            $table->json('seo')->nullable();
            $table->string('status')->default('draft');
            $this->addUserReference($table, 'created_by');
            $this->addUserReference($table, 'updated_by');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['set_uuid', 'slug']);
            $table->index(['set_uuid', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_contents');
    }

    private function addUserReference(Blueprint $table, string $column): void
    {
        match (config('ceemes.users.key_type', 'integer')) {
            'uuid' => $table->uuid($column)->nullable(),
            'ulid' => $table->ulid($column)->nullable(),
            'integer' => $table->unsignedBigInteger($column)->nullable(),
            default => throw new InvalidArgumentException('Unsupported Ceemes user key type.'),
        };
        if (config('ceemes.users.foreign_keys', true)) {
            $table->foreign($column)->references((string) config('ceemes.users.key', 'id'))->on((string) config('ceemes.users.table', 'users'))->nullOnDelete()->cascadeOnUpdate();
        }
    }
};
