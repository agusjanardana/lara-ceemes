<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ceemes_super_admins', function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            match (config('ceemes.users.key_type', 'integer')) {
                'uuid' => $table->uuid('user_key'),
                'ulid' => $table->ulid('user_key'),
                'integer' => $table->unsignedBigInteger('user_key'),
                default => throw new InvalidArgumentException('Unsupported Ceemes user key type.'),
            };
            $table->timestamps();
            $table->unique('user_key');
            if (config('ceemes.users.foreign_keys', true)) {
                $table->foreign('user_key')->references((string) config('ceemes.users.key', 'id'))->on((string) config('ceemes.users.table', 'users'))->cascadeOnDelete()->cascadeOnUpdate();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ceemes_super_admins');
    }
};
