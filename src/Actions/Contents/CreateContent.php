<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Contents;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Enums\ContentStatus;
use LaraCeemes\Events\ContentCreated;
use LaraCeemes\Events\ContentPublished;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Set;
use LaraCeemes\Support\ContentCacheInvalidator;
use LaraCeemes\Support\ContentDataValidator;
use LaraCeemes\Support\ContentUri;

final class CreateContent extends Action
{
    public function __construct(
        private readonly ContentDataValidator $contentData,
        private readonly ContentCacheInvalidator $cache,
        private readonly ContentUri $contentUri,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(Set $set, array $data): Content
    {
        if (trim((string) ($data['slug'] ?? '')) === '') {
            $data['slug'] = Str::slug((string) ($data['title'] ?? ''));
        }
        $setUuid = $set->uuid;
        $data['uri'] = $this->contentUri->normalize($data['uri'] ?? null, $set, $data['slug']);
        $adminPrefix = preg_quote(trim((string) config('ceemes.admin.prefix', 'admin'), '/'), '#');

        $validated = Validator::make($data, [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('ceemes_contents', 'slug')
                    ->where('set_uuid', $setUuid),
            ],
            'uri' => [
                'required',
                'string',
                'max:255',
                'regex:#^/(?:[a-z0-9]+(?:-[a-z0-9]+)*(?:/[a-z0-9]+(?:-[a-z0-9]+)*)*)?$#',
                "not_regex:#^/{$adminPrefix}(?:/|$)#",
                Rule::unique('ceemes_contents', 'uri'),
            ],
            'data' => ['sometimes', 'array'],
            'seo' => ['nullable', 'array'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'created_by' => ['nullable'],
            'updated_by' => ['nullable'],
        ])->validate();

        $validated['data'] = $this->contentData->validate(
            $set,
            is_array($validated['data'] ?? null) ? $validated['data'] : [],
        );
        $validated['seo'] = $this->contentData->validateSeo(
            is_array($validated['seo'] ?? null) ? $validated['seo'] : null,
        );
        $validated['status'] ??= ContentStatus::Draft->value;
        $validated['created_by'] ??= Auth::id();
        $validated['updated_by'] ??= $validated['created_by'];

        return $this->transaction(function () use ($set, $validated): Content {
            $content = $set->contents()->create($validated);

            $this->cache->content($content);
            event(new ContentCreated($content));

            if ($content->status === ContentStatus::Published) {
                event(new ContentPublished($content));
            }

            return $content;
        });
    }
}
