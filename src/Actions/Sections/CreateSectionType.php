<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use LaraCeemes\Actions\Action;
use LaraCeemes\Models\SectionType;

final class CreateSectionType extends Action
{
    /** @param array<string, mixed> $data */
    public function execute(array $data): SectionType
    {
        $data['handle'] ??= Str::slug((string) ($data['name'] ?? ''));

        $validated = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'handle' => ['required', 'alpha_dash:ascii', 'max:255', Rule::unique('ceemes_section_types', 'handle')],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
        ])->validate();

        return $this->transaction(fn (): SectionType => SectionType::query()->create($validated));
    }
}
