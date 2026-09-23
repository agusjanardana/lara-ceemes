<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Navigations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Support\CeemesCache;

final class UpdateNavigation extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(Navigation $navigation, array $data): Navigation
    {
        $validated = Validator::make($data, [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'handle' => [
                'sometimes', 'required', 'alpha_dash:ascii', 'max:255',
                Rule::unique('ceemes_navigations', 'handle')
                    ->where('site_uuid', $navigation->site_uuid)
                    ->ignore($navigation->uuid, 'uuid'),
            ],
        ])->validate();

        return $this->transaction(function () use ($navigation, $validated): Navigation {
            $oldHandle = $navigation->handle;
            $navigation->update($validated);
            $this->cache->forget("site:{$navigation->site_uuid}:navigation:{$oldHandle}");
            $this->cache->forget("site:{$navigation->site_uuid}:navigation:{$navigation->handle}");

            return $navigation->refresh();
        });
    }
}
