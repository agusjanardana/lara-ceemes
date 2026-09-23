<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Contents;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Enums\ContentStatus;
use LaraCeemes\Events\ContentPublished;
use LaraCeemes\Events\ContentUpdated;
use LaraCeemes\Models\Content;
use LaraCeemes\Support\ContentCacheInvalidator;
use LaraCeemes\Support\ContentDataValidator;
use LaraCeemes\Support\ContentUri;

final class UpdateContent extends Action
{
    public function __construct(
        private readonly ContentDataValidator $contentData,
        private readonly ContentCacheInvalidator $cache,
        private readonly ContentUri $contentUri,
    ) {}

    /** @param array<string, mixed> $data */
    public function execute(Content $content, array $data): Content
    {
        if (array_key_exists('uri', $data)) {
            $data['uri'] = $this->contentUri->normalize($data['uri'], $content->set, (string) ($data['slug'] ?? $content->slug));
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
                Rule::unique('ceemes_contents', 'slug')
                    ->where('set_uuid', $content->set_uuid)
                    ->where('site_uuid', $content->site_uuid)
                    ->ignore($content->uuid, 'uuid'),
            ],
            'uri' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'regex:#^/(?:[a-z0-9]+(?:-[a-z0-9]+)*(?:/[a-z0-9]+(?:-[a-z0-9]+)*)*)?$#',
                "not_regex:#^/{$adminPrefix}(?:/|$)#",
                Rule::unique('ceemes_contents', 'uri')
                    ->where('site_uuid', $content->site_uuid)
                    ->ignore($content->uuid, 'uuid'),
            ],
            'data' => ['sometimes', 'array'],
            'seo' => ['nullable', 'array'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'updated_by' => ['nullable'],
        ])->validate();

        if (array_key_exists('data', $validated)) {
            $validated['data'] = $this->contentData->validate(
                $content->set,
                is_array($validated['data']) ? $validated['data'] : [],
            );
        }

        if (array_key_exists('seo', $data)) {
            $validated['seo'] = $this->contentData->validateSeo(
                is_array($validated['seo'] ?? null) ? $validated['seo'] : null,
            );
        }

        $validated['updated_by'] ??= Auth::id();

        return $this->transaction(function () use ($content, $validated): Content {
            $previousSlug = $content->slug;
            $previousStatus = $content->status;
            $content->update($validated);
            $content->refresh();

            $this->cache->content($content, $previousSlug);
            event(new ContentUpdated($content));

            if ($previousStatus !== ContentStatus::Published && $content->status === ContentStatus::Published) {
                event(new ContentPublished($content));
            }

            return $content;
        });
    }
}
