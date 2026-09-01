<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Contents\CreateContent;
use LaraCeemes\Actions\Contents\DeleteContent;
use LaraCeemes\Actions\Contents\UpdateContent;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\CategoryGroup;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Media;
use LaraCeemes\Models\Section;
use LaraCeemes\Models\SectionType;
use LaraCeemes\Models\Set;

final class ContentController extends AdminController
{
    public function __construct(private readonly FieldRegistry $registry) {}

    public function index(Request $request, Set $set): View
    {
        $search = $request->string('q')->trim()->toString();
        $status = $request->string('status')->toString();

        return $this->render('ceemes::admin.contents.index', [
            'set' => $set,
            'contents' => $set->contents()
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                }))
                ->when(in_array($status, ['draft', 'published'], true), fn ($query) => $query->where('status', $status))
                ->latest('updated_at')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(Set $set): View
    {
        return $this->render('ceemes::admin.contents.form', [
            'set' => $set,
            'fields' => $set->fields()->get(),
            'content' => null,
            'sectionTypes' => SectionType::query()->with('fields')->orderBy('name')->get(),
            'sectionFields' => collect(),
            ...$this->fieldResources(),
        ]);
    }

    public function store(Request $request, Set $set, CreateContent $action): RedirectResponse
    {
        $data = $request->all();
        $data['data'] = $this->contentFormData($request, $set);
        $data['seo'] = $this->seoFormData($request);
        $content = $action->execute($set, $data);

        return $this->success('ceemes.admin.contents.edit', 'Content created. Continue editing the sections below.', $content);
    }

    public function edit(Content $content): View
    {
        $fields = $content->set->fields()->get();
        $sectionFields = $fields->where('type', 'sections');
        if ($sectionFields->isEmpty()) {
            $sectionFields = collect([(object) [
                'handle' => 'sections',
                'label' => 'Page Sections',
                'config' => ['instructions' => 'Susun reusable Sections untuk Content ini.'],
            ]]);
        }

        return $this->render('ceemes::admin.contents.form', [
            'set' => $content->set,
            'fields' => $fields,
            'content' => $content,
            'sectionTypes' => SectionType::query()->with('fields')->orderBy('name')->get(),
            'sectionFields' => $sectionFields,
            'reusableSections' => Section::query()->with('sectionType')->orderBy('name')->get(),
            ...$this->fieldResources(),
        ]);
    }

    public function update(Request $request, Content $content, UpdateContent $action): RedirectResponse
    {
        $data = $request->all();
        $data['data'] = $this->contentFormData($request, $content->set);
        $data['seo'] = $this->seoFormData($request);
        $action->execute($content, $data);

        return $this->success('ceemes.admin.contents.edit', 'Content updated.', $content);
    }

    public function destroy(Content $content, DeleteContent $action): RedirectResponse
    {
        $set = $content->set;
        $action->execute($content);

        return $this->success('ceemes.admin.contents.index', 'Content deleted.', $set);
    }

    /** @return array<string, mixed> */
    private function contentFormData(Request $request, Set $set): array
    {
        $raw = $request->input('data');

        if (is_string($raw)) {
            return $this->jsonObject($raw, 'data');
        }

        $raw = is_array($raw) ? $raw : [];

        return $this->normalizeFieldFormData($raw, $set->fields()->get());
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
            'categoryGroups' => CategoryGroup::query()->with('categories')->orderBy('name')->get(),
            'contentOptions' => Content::query()
                ->with('set:uuid,name,handle')
                ->orderBy('title')
                ->get(['uuid', 'set_uuid', 'title', 'slug', 'uri', 'status']),
            'contentSets' => Set::query()->orderBy('name')->get(['uuid', 'name', 'handle']),
            'fieldTypes' => array_keys($this->registry->all()),
            'sets' => Set::query()->orderBy('name')->get(),
        ];
    }
}
