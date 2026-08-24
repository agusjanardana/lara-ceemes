<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LaraCeemes\Actions\Settings\SetSetting;
use LaraCeemes\Models\Setting;

final class SettingController extends AdminController
{
    public function index(): View
    {
        return $this->render('ceemes::admin.settings', [
            'settings' => Setting::query()->orderBy('group')->orderBy('key')->get(),
        ]);
    }

    public function store(Request $request, SetSetting $action): RedirectResponse
    {
        $type = $request->string('type', 'string')->toString();
        $raw = $request->input('value');
        $value = match ($type) {
            'integer' => (int) $raw,
            'float' => (float) $raw,
            'boolean' => filter_var($raw, FILTER_VALIDATE_BOOL),
            'array' => $this->jsonObject($raw, 'value'),
            'null' => null,
            default => is_string($raw) ? trim($raw, "\"\n\r") : '',
        };
        $action->execute(
            $request->string('key')->toString(),
            $value,
            $type,
            $request->boolean('autoload'),
        );

        return $this->success('ceemes.admin.settings.index', 'Setting saved.');
    }
}
