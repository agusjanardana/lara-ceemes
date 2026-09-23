<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_media_folders', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('parent_uuid')->nullable();
            $table->string('disk');
            $table->string('name');
            $table->string('path');
            $this->addUserReference($table, 'created_by');
            $table->timestamps();
            $table->unique(['disk', 'path']);
            $table->foreign('parent_uuid')->references('uuid')->on('ceemes_media_folders')->restrictOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_media_folders');
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
