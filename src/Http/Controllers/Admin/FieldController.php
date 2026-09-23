<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Sets\CreateSetField;
use LaraCeemes\Actions\Sets\DeleteSetField;
use LaraCeemes\Actions\Sets\UpdateSetField;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Set;
use LaraCeemes\Models\SetField;
use LaraCeemes\Support\FieldConfiguration;

final class FieldController extends AdminController
{
    public function __construct(private readonly FieldConfiguration $fieldConfiguration) {}

    public function store(Request $request, Set $set, CreateSetField $action): RedirectResponse
    {
        $data = $request->all();
        $data['config'] = $this->fieldConfiguration->fromRequest($request, $set->fields()->get());
        $action->execute($set, $data);

        return $this->redirectToContent($request, $set, 'Fixed Field ditambahkan.');
    }

    public function update(Request $request, SetField $field, UpdateSetField $action): RedirectResponse
    {
        $data = $request->all();
        $data['config'] = $this->fieldConfiguration->fromRequest(
            $request,
            $field->set->fields()->where('uuid', '!=', $field->uuid)->get(),
        );
        $action->execute($field, $data);

        return $this->redirectToContent($request, $field->set, 'Fixed Field diperbarui.');
    }

    public function destroy(Request $request, SetField $field, DeleteSetField $action): RedirectResponse
    {
        $set = $field->set;
        $action->execute($field);

        return $this->redirectToContent($request, $set, 'Fixed Field dihapus.');
    }

    private function redirectToContent(Request $request, Set $set, string $message): RedirectResponse
    {
        $contentUuid = $request->string('redirect_content_uuid')->toString();
        $content = Content::query()
            ->where('set_uuid', $set->uuid)
            ->when($contentUuid !== '', fn ($query) => $query->whereKey($contentUuid))
            ->first();

        if ($content !== null) {
            return $this->success('ceemes.admin.contents.edit', $message, $content);
        }

        return $this->success('ceemes.admin.contents.index', $message, $set);
    }
}
