<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Models\BlueprintField;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\SectionField;

abstract class AdminController extends Controller
{
    /** @param array<string, mixed> $data */
    protected function render(string $view, array $data = []): View
    {
        return app(Factory::class)->make($view, [
            'sidebarCollections' => Collection::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['uuid', 'name', 'handle']),
            ...$data,
        ]);
    }

    /** @return array<string, mixed> */
    protected function jsonObject(mixed $value, string $field): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode(is_string($value) && $value !== '' ? $value : '{}', true);

        if (! is_array($decoded)) {
            throw ValidationException::withMessages([$field => 'Must contain a valid JSON object.']);
        }

        return $decoded;
    }

    protected function success(string $route, string $message, mixed ...$parameters): RedirectResponse
    {
        return redirect()->route($route, $parameters)->with('success', $message);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  iterable<int, BlueprintField|SectionField>  $fields
     * @return array<string, mixed>
     */
    protected function normalizeFieldFormData(array $data, iterable $fields, string $prefix = 'data'): array
    {
        foreach ($fields as $field) {
            $value = $data[$field->handle] ?? null;

            if (in_array($field->type, ['group', 'seo'], true) && is_string($value)) {
                if (trim($value) === '') {
                    $data[$field->handle] = [];

                    continue;
                }

                $decoded = json_decode($value, true);

                if (! is_array($decoded)) {
                    throw ValidationException::withMessages([
                        "{$prefix}.{$field->handle}" => 'Must contain valid JSON.',
                    ]);
                }

                $data[$field->handle] = $decoded;
            }
        }

        return $data;
    }
}
