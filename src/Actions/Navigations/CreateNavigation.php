<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Navigations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Support\CeemesCache;
use LaraCeemes\Support\SiteContext;

final class CreateNavigation extends Action
{
    public function __construct(private readonly CeemesCache $cache, private readonly SiteContext $sites) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data): Navigation
    {
        $data['handle'] ??= Str::slug((string) ($data['name'] ?? ''));
        $data['site_uuid'] = $this->sites->current()->uuid;
        $siteUuid = $data['site_uuid'];
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'handle' => [
                'required', 'alpha_dash:ascii', 'max:255',
                Rule::unique('ceemes_navigations', 'handle')->where('site_uuid', $siteUuid),
            ],
            'site_uuid' => ['required', 'uuid'],
        ])->validate();

        return $this->transaction(function () use ($validated): Navigation {
            $navigation = Navigation::query()->create($validated);
            $this->cache->forget("site:{$navigation->site_uuid}:navigation:{$navigation->handle}");

            return $navigation;
        });
    }
}
