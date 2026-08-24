<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Entries;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Enums\EntryStatus;
use LaraCeemes\Events\EntryPublished;
use LaraCeemes\Events\EntryUpdated;
use LaraCeemes\Models\Entry;
use LaraCeemes\Support\ContentCacheInvalidator;
use LaraCeemes\Support\EntryDataValidator;
use LaraCeemes\Support\EntryUri;

final class UpdateEntry extends Action
{
    public function __construct(
        private readonly EntryDataValidator $entryData,
        private readonly ContentCacheInvalidator $cache,
        private readonly EntryUri $entryUri,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(Entry $entry, array $data): Entry
    {
        if (array_key_exists('uri', $data)) {
            $data['uri'] = $this->entryUri->normalize($data['uri'], $entry->collection, (string) ($data['slug'] ?? $entry->slug));
        }

        $adminPrefix = preg_quote(trim((string) config('ceemes.admin.prefix', 'admin'), '/'), '#');
        $validated = Validator::make($data, [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('ceemes_entries', 'slug')
                    ->where('collection_uuid', $entry->collection_uuid)
                    ->ignore($entry->uuid, 'uuid'),
            ],
            'uri' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:#^/(?:[a-z0-9]+(?:-[a-z0-9]+)*(?:/[a-z0-9]+(?:-[a-z0-9]+)*)*)?$#',
                "not_regex:#^/{$adminPrefix}(?:/|$)#",
                Rule::unique('ceemes_entries', 'uri')->ignore($entry->uuid, 'uuid'),
            ],
            'data' => ['sometimes', 'array'],
            'seo' => ['nullable', 'array'],
            'status' => ['sometimes', Rule::enum(EntryStatus::class)],
            'updated_by' => ['nullable'],
        ])->validate();

        if (array_key_exists('data', $validated)) {
            $validated['data'] = $this->entryData->validate(
                $entry->blueprint,
                is_array($validated['data']) ? $validated['data'] : [],
            );
        }

        if (array_key_exists('seo', $data)) {
            $validated['seo'] = $this->entryData->validateSeo(
                is_array($validated['seo'] ?? null) ? $validated['seo'] : null,
            );
        }

        $validated['updated_by'] ??= Auth::id();

        return $this->transaction(function () use ($entry, $validated): Entry {
            $previousSlug = $entry->slug;
            $previousStatus = $entry->status;
            $entry->update($validated);
            $entry->refresh();

            $this->cache->entry($entry, $previousSlug);
            event(new EntryUpdated($entry));

            if ($previousStatus !== EntryStatus::Published && $entry->status === EntryStatus::Published) {
                event(new EntryPublished($entry));
            }

            return $entry;
        });
    }
}
