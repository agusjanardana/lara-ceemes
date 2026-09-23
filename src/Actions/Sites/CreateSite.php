<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sites;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Site;
use LaraCeemes\Support\SiteContext;

final class CreateSite extends Action
{
    public function __construct(private readonly SiteContext $sites) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data): Site
    {
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                'required',
                'string',
                'max:32',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('ceemes_sites', 'handle'),
            ],
            'locale' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})?$/'],
            'is_default' => ['sometimes', 'boolean'],
            'is_enabled' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();
        $validated['is_default'] ??= false;
        $validated['is_enabled'] ??= true;

        if ($validated['is_default'] && ! $validated['is_enabled']) {
            throw ValidationException::withMessages(['is_enabled' => 'Default Site harus tetap aktif.']);
        }

        return $this->transaction(function () use ($validated): Site {
            if ($validated['is_default']) {
                Site::query()->update(['is_default' => false]);
            }

            $site = Site::query()->create($validated);
            $this->sites->forget();

            return $site;
        });
    }
}
