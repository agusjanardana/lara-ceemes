<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use LaraCeemes\Models\Section;

final class SectionLibraryController extends AdminController
{
    public function __invoke(): View
    {
        return $this->render('ceemes::admin.sections.index', [
            'sections' => Section::query()
                ->with(['sectionType', 'contents.set'])
                ->withCount('contents')
                ->latest('updated_at')
                ->get(),
        ]);
    }
}
