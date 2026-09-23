<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $siteUuid = DB::table('ceemes_sites')->where('is_default', true)->value('uuid');
        if (! is_string($siteUuid)) {
            throw new RuntimeException('Lara Ceemes requires a default Site before adding multisite scope.');
        }

        foreach (['ceemes_contents', 'ceemes_sections', 'ceemes_navigations'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->uuid('site_uuid')->nullable()->index();
            });
            DB::table($tableName)->whereNull('site_uuid')->update(['site_uuid' => $siteUuid]);
            Schema::table($tableName, function (Blueprint $table): void {
                $table->uuid('site_uuid')->nullable(false)->change();
                $table->foreign('site_uuid')->references('uuid')->on('ceemes_sites')->restrictOnDelete()->cascadeOnUpdate();
            });
        }

        Schema::table('ceemes_contents', function (Blueprint $table): void {
            $table->dropUnique('ceemes_contents_uri_unique');
            $table->dropUnique('ceemes_contents_set_uuid_slug_unique');
            $table->unique(['site_uuid', 'uri']);
            $table->unique(['site_uuid', 'set_uuid', 'slug']);
            $table->index(['site_uuid', 'set_uuid', 'status']);
        });

        Schema::table('ceemes_sections', function (Blueprint $table): void {
            $table->dropUnique('ceemes_sections_handle_unique');
            $table->unique(['site_uuid', 'handle']);
        });

        Schema::table('ceemes_navigations', function (Blueprint $table): void {
            $table->dropUnique('ceemes_navigations_handle_unique');
            $table->unique(['site_uuid', 'handle']);
        });
    }

    public function down(): void
    {
        Schema::table('ceemes_contents', function (Blueprint $table): void {
            $table->dropUnique(['site_uuid', 'uri']);
            $table->dropUnique(['site_uuid', 'set_uuid', 'slug']);
            $table->dropIndex(['site_uuid', 'set_uuid', 'status']);
            $table->unique('uri');
            $table->unique(['set_uuid', 'slug']);
        });
        Schema::table('ceemes_sections', function (Blueprint $table): void {
            $table->dropUnique(['site_uuid', 'handle']);
            $table->unique('handle');
        });
        Schema::table('ceemes_navigations', function (Blueprint $table): void {
            $table->dropUnique(['site_uuid', 'handle']);
            $table->unique('handle');
        });

        foreach (['ceemes_contents', 'ceemes_sections', 'ceemes_navigations'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropForeign(['site_uuid']);
                $table->dropIndex(['site_uuid']);
                $table->dropColumn('site_uuid');
            });
        }
    }
};
