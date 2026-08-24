<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Navigations;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\Navigation;
use LaraCeemes\Support\CeemesCache;

final class CreateNavigation extends Action
{
    public function __construct(private readonly CeemesCache $cache) {}

    /** @param array<string, mixed> $data */
    public function execute(array $data): Navigation
    {
        $data['handle'] ??= Str::slug((string) ($data['name'] ?? ''));
        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'alpha_dash:ascii', 'max:255', Rule::unique('ceemes_navigations', 'handle')],
        ])->validate();

        return $this->transaction(function () use ($validated): Navigation {
            $navigation = Navigation::query()->create($validated);
            $this->cache->forget("navigation:{$navigation->handle}");

            return $navigation;
        });
    }
}
