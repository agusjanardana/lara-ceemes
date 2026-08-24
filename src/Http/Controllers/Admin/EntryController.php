<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Entries\CreateEntry;
use LaraCeemes\Actions\Entries\DeleteEntry;
use LaraCeemes\Actions\Entries\UpdateEntry;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\Entry;
use LaraCeemes\Models\Media;
use LaraCeemes\Models\SectionType;
use LaraCeemes\Models\Taxonomy;

final class EntryController extends AdminController
{
    public function index(Request $request, Collection $collection): View
    {
        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->toString();
        $blueprint = $request->string('blueprint')->toString();

        return $this->render('ceemes::admin.entries.index', [
            'collection' => $collection,
            'blueprints' => $collection->blueprints()->orderBy('name')->get(),
            'entries' => $collection->entries()
                ->with('blueprint')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                }))
                ->when(in_array($status, ['draft', 'published'], true), fn ($query) => $query->where('status', $status))
                ->when($blueprint !== '', fn ($query) => $query->where('blueprint_uuid', $blueprint))
                ->latest('updated_at')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(Collection $collection): View
    {
        return $this->render('ceemes::admin.entries.form', [
            'collection' => $collection,
            'blueprints' => $collection->blueprints()->with('fields')->get(),
            'entry' => null,
            'sectionTypes' => collect(),
            'sectionFields' => collect(),
            ...$this->fieldResources(),
        ]);
    }

    public function store(Request $request, Collection $collection, CreateEntry $action): RedirectResponse
    {
        $blueprint = $collection->blueprints()->where('uuid', $request->input('blueprint_uuid'))->firstOrFail();
        $data = $request->all();
        $data['data'] = $this->entryFormData($request, $blueprint);
        $data['seo'] = $this->seoFormData($request);
        $entry = $action->execute($blueprint, $data);

        return $this->success('ceemes.admin.entries.edit', 'Entry created. Continue editing the content below.', $entry);
    }

    public function edit(Entry $entry): View
    {
        return $this->render('ceemes::admin.entries.form', [
            'collection' => $entry->collection,
            'blueprints' => collect([$entry->blueprint]),
            'entry' => $entry,
            'sectionTypes' => SectionType::query()->with('fields')->orderBy('name')->get(),
            'sectionFields' => $entry->blueprint->fields->where('type', 'sections'),
            ...$this->fieldResources(),
        ]);
    }

    public function update(Request $request, Entry $entry, UpdateEntry $action): RedirectResponse
    {
        $data = $request->all();
        $data['data'] = $this->entryFormData($request, $entry->blueprint);
        $data['seo'] = $this->seoFormData($request);
        $action->execute($entry, $data);

        return $this->success('ceemes.admin.entries.edit', 'Entry updated.', $entry);
    }

    public function destroy(Entry $entry, DeleteEntry $action): RedirectResponse
    {
        $collection = $entry->collection;
        $action->execute($entry);

        return $this->success('ceemes.admin.entries.index', 'Entry deleted.', $collection);
    }

    /** @return array<string, mixed> */
    private function entryFormData(Request $request, Blueprint $blueprint): array
    {
        $raw = $request->input('data');

        if (is_string($raw)) {
            return $this->jsonObject($raw, 'data');
        }

        if (! is_array($raw)) {
            $blueprintData = $request->input('blueprint_data', []);
            $raw = is_array($blueprintData) && is_array($blueprintData[$blueprint->uuid] ?? null)
                ? $blueprintData[$blueprint->uuid]
                : [];
        }

        return $this->normalizeFieldFormData($raw, $blueprint->fields);
    }

    /** @return array<string, mixed> */
    private function seoFormData(Request $request): array
    {
        $seo = $request->input('seo', []);

        if (is_string($seo)) {
            return $this->jsonObject($seo, 'seo');
        }

        return is_array($seo) ? $seo : [];
    }

    /** @return array<string, mixed> */
    private function fieldResources(): array
    {
        return [
            'mediaItems' => Media::query()->orderBy('original_filename')->get(),
            'taxonomies' => Taxonomy::query()->with('terms')->orderBy('name')->get(),
            'entryOptions' => Entry::query()
                ->with('collection:uuid,name,handle')
                ->orderBy('title')
                ->get(['uuid', 'collection_uuid', 'title', 'slug', 'uri', 'status']),
            'entryCollections' => Collection::query()->orderBy('name')->get(['uuid', 'name', 'handle']),
        ];
    }
}
