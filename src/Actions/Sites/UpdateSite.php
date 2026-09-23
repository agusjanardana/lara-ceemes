<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sites;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Site;
use LaraCeemes\Support\SiteContext;

final class UpdateSite extends Action
{
    public function __construct(private readonly SiteContext $sites) {}

    /** @param array<string, mixed> $data */
    public function execute(Site $site, array $data): Site
    {
        $validated = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'handle' => [
                'sometimes',
                'required',
                'string',
                'max:32',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('ceemes_sites', 'handle')->ignore($site->uuid, 'uuid'),
            ],
            'locale' => ['sometimes', 'required', 'string', 'max:20', 'regex:/^[A-Za-z]{2,3}(?:[-_][A-Za-z0-9]{2,8})?$/'],
            'is_default' => ['sometimes', 'boolean'],
            'is_enabled' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ])->validate();

        if ($site->is_default && array_key_exists('is_default', $validated) && ! $validated['is_default']) {
            throw ValidationException::withMessages(['is_default' => 'Pilih Site lain sebagai default sebelum melepas status default Site ini.']);
        }
        if ($site->is_default && array_key_exists('is_enabled', $validated) && ! $validated['is_enabled']) {
            throw ValidationException::withMessages(['is_enabled' => 'Default Site tidak dapat dinonaktifkan.']);
        }
        if (($validated['is_default'] ?? false) === true && ! ($validated['is_enabled'] ?? $site->is_enabled)) {
            throw ValidationException::withMessages(['is_enabled' => 'Default Site harus tetap aktif.']);
        }

        return $this->transaction(function () use ($site, $validated): Site {
            if (($validated['is_default'] ?? false) === true) {
                Site::query()->where('uuid', '!=', $site->uuid)->update(['is_default' => false]);
            }

            $site->update($validated);
            $this->sites->forget();

            return $site->refresh();
        });
    }
}
