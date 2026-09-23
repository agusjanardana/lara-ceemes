<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_media', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('folder_uuid')->nullable();
            $table->string('disk');
            $table->string('directory')->default('');
            $table->string('filename');
            $table->string('original_filename');
            $table->string('extension', 32);
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('title')->nullable();
            $table->text('alt')->nullable();
            $table->text('caption')->nullable();
            $this->addUserReference($table, 'uploaded_by');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['disk', 'directory', 'filename'], 'ceemes_media_path_unique');
            $table->index(['mime_type', 'created_at']);
            $table->foreign('folder_uuid')->references('uuid')->on('ceemes_media_folders')->nullOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_media');
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
