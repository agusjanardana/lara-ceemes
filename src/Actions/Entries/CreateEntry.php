<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Entries;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Enums\EntryStatus;
use LaraCeemes\Events\EntryCreated;
use LaraCeemes\Events\EntryPublished;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Models\Entry;
use LaraCeemes\Support\ContentCacheInvalidator;
use LaraCeemes\Support\EntryDataValidator;
use LaraCeemes\Support\EntryUri;

final class CreateEntry extends Action
{
    public function __construct(
        private readonly EntryDataValidator $entryData,
        private readonly ContentCacheInvalidator $cache,
        private readonly EntryUri $entryUri,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(Blueprint $blueprint, array $data): Entry
    {
        if (trim((string) ($data['slug'] ?? '')) === '') {
            $data['slug'] = Str::slug((string) ($data['title'] ?? ''));
        }
        $collectionUuid = $blueprint->collection_uuid;
        $data['uri'] = $this->entryUri->normalize($data['uri'] ?? null, $blueprint->collection, $data['slug']);
        $adminPrefix = preg_quote(trim((string) config('ceemes.admin.prefix', 'admin'), '/'), '#');

        $validated = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('ceemes_entries', 'slug')
                    ->where('collection_uuid', $collectionUuid),
            ],
            'uri' => [
                'required',
                'string',
                'max:255',
                'regex:#^/(?:[a-z0-9]+(?:-[a-z0-9]+)*(?:/[a-z0-9]+(?:-[a-z0-9]+)*)*)?$#',
                "not_regex:#^/{$adminPrefix}(?:/|$)#",
                Rule::unique('ceemes_entries', 'uri'),
            ],
            'data' => ['sometimes', 'array'],
            'seo' => ['nullable', 'array'],
            'status' => ['sometimes', Rule::enum(EntryStatus::class)],
            'created_by' => ['nullable'],
            'updated_by' => ['nullable'],
        ])->validate();

        $validated['data'] = $this->entryData->validate(
            $blueprint,
            is_array($validated['data'] ?? null) ? $validated['data'] : [],
        );
        $validated['seo'] = $this->entryData->validateSeo(
            is_array($validated['seo'] ?? null) ? $validated['seo'] : null,
        );
        $validated['status'] ??= EntryStatus::Draft->value;
        $validated['created_by'] ??= Auth::id();
        $validated['updated_by'] ??= $validated['created_by'];

        return $this->transaction(function () use ($blueprint, $validated): Entry {
            $entry = $blueprint->entries()->create([
                ...$validated,
                'collection_uuid' => $blueprint->collection_uuid,
            ]);

            $this->cache->entry($entry);
            event(new EntryCreated($entry));

            if ($entry->status === EntryStatus::Published) {
                event(new EntryPublished($entry));
            }

            return $entry;
        });
    }
}
