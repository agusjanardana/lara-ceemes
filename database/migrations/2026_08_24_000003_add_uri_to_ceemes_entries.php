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
        if (Schema::hasColumn('ceemes_entries', 'uri')) {
            return;
        }

        Schema::table('ceemes_entries', function (Blueprint $table): void {
            $table->string('uri')->nullable()->after('slug')->index();
        });

        $used = [];

        DB::table('ceemes_entries')
            ->join('ceemes_collections', 'ceemes_collections.uuid', '=', 'ceemes_entries.collection_uuid')
            ->select(['ceemes_entries.uuid', 'ceemes_entries.slug', 'ceemes_collections.handle', 'ceemes_collections.route'])
            ->orderBy('ceemes_entries.created_at')
            ->get()
            ->each(function (object $entry) use (&$used): void {
                $pattern = is_string($entry->route) && str_contains($entry->route, '{slug}')
                    ? $entry->route
                    : '/{slug}';
                $uri = '/'.ltrim(str_replace('{slug}', (string) $entry->slug, $pattern), '/');

                if (isset($used[$uri])) {
                    $uri = '/'.trim((string) $entry->handle, '/').'/'.(string) $entry->slug;
                }

                $used[$uri] = true;
                DB::table('ceemes_entries')->where('uuid', $entry->uuid)->update(['uri' => $uri]);
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('ceemes_entries', 'uri')) {
            Schema::table('ceemes_entries', function (Blueprint $table): void {
                $table->dropColumn('uri');
            });
        }
    }
};
