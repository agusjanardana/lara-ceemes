<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Blueprints\CreateBlueprintField;
use LaraCeemes\Actions\Blueprints\DeleteBlueprintField;
use LaraCeemes\Actions\Blueprints\UpdateBlueprintField;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\Blueprint;
use LaraCeemes\Models\BlueprintField;
use LaraCeemes\Models\Collection;
use LaraCeemes\Models\SectionType;

final class FieldController extends AdminController
{
    public function __construct(private readonly FieldRegistry $registry) {}

    public function index(Blueprint $blueprint): View
    {
        return $this->render('ceemes::admin.fields.index', [
            'blueprint' => $blueprint,
            'fields' => $blueprint->fields()->get(),
            'fieldTypes' => array_keys($this->registry->all()),
            'sectionTypes' => SectionType::query()->orderBy('name')->get(),
            'collections' => Collection::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Blueprint $blueprint, CreateBlueprintField $action): RedirectResponse
    {
        $data = $request->all();
        $data['config'] = $this->fieldConfig($request);
        $action->execute($blueprint, $data);

        return $this->success('ceemes.admin.fields.index', 'Field created.', $blueprint);
    }

    public function update(Request $request, BlueprintField $field, UpdateBlueprintField $action): RedirectResponse
    {
        $data = $request->all();
        $data['config'] = $this->fieldConfig($request);
        $action->execute($field, $data);

        return $this->success('ceemes.admin.fields.index', 'Field updated.', $field->blueprint);
    }

    public function destroy(BlueprintField $field, DeleteBlueprintField $action): RedirectResponse
    {
        $blueprint = $field->blueprint;
        $action->execute($field);

        return $this->success('ceemes.admin.fields.index', 'Field deleted.', $blueprint);
    }

    /** @return array<string, mixed> */
    private function fieldConfig(Request $request): array
    {
        $input = $request->input('config', []);

        if (is_string($input)) {
            return $this->jsonObject($input, 'config');
        }

        $advanced = $this->jsonObject($request->input('advanced_config'), 'advanced_config');
        $config = [...$advanced, ...(is_array($input) ? $input : [])];
        $optionsText = is_string($config['options_text'] ?? null) ? $config['options_text'] : '';
        unset($config['options_text']);

        $config['required'] = $request->boolean('config.required');

        if ($request->has('config.multiple')) {
            $config['multiple'] = $request->boolean('config.multiple');
        }

        if ($request->string('type')->toString() === 'sections') {
            $config['allowed'] = is_array($config['allowed'] ?? null)
                ? array_values(array_filter($config['allowed'], 'is_string'))
                : [];
        } else {
            unset($config['allowed']);
        }

        if ($request->string('type')->toString() === 'entry') {
            $collection = is_string($config['collection'] ?? null) ? $config['collection'] : '';

            if (! Collection::query()->where('handle', $collection)->exists()) {
                throw ValidationException::withMessages([
                    'config.collection' => 'Pilih Collection sumber untuk Entry field.',
                ]);
            }
        } else {
            unset($config['collection']);
        }

        if ($request->string('type')->toString() === 'repeater') {
            $config['fields'] = $this->repeaterFields($config['fields'] ?? []);

            foreach (['min_rows', 'max_rows'] as $key) {
                $value = filter_var($config[$key] ?? null, FILTER_VALIDATE_INT);
                if ($value === false || $value < 0) {
                    unset($config[$key]);
                } else {
                    $config[$key] = $value;
                }
            }

            if (isset($config['min_rows'], $config['max_rows']) && $config['max_rows'] < $config['min_rows']) {
                throw ValidationException::withMessages([
                    'config.max_rows' => 'Maximum item tidak boleh lebih kecil dari minimum item.',
                ]);
            }
        } else {
            unset($config['fields'], $config['min_rows'], $config['max_rows']);
        }

        foreach (['placeholder', 'instructions'] as $key) {
            if (trim((string) ($config[$key] ?? '')) === '') {
                unset($config[$key]);
            }
        }

        if (trim($optionsText) !== '') {
            $config['options'] = collect(preg_split('/\r\n|\r|\n/', $optionsText) ?: [])
                ->filter(fn (string $line): bool => trim($line) !== '')
                ->mapWithKeys(function (string $line): array {
                    $parts = explode(':', $line, 2);
                    $value = trim($parts[0]);
                    $label = trim($parts[1] ?? $value);

                    return [$value => $label];
                })
                ->all();
        } else {
            unset($config['options']);
        }

        return $config;
    }

    /** @return array<int, array<string, mixed>> */
    private function repeaterFields(mixed $input): array
    {
        $allowed = array_values(array_diff(array_keys($this->registry->all()), ['repeater', 'sections', 'group', 'seo']));
        $fields = [];

        foreach (is_array($input) ? $input : [] as $index => $field) {
            if (! is_array($field)) {
                continue;
            }

            $label = trim((string) ($field['label'] ?? ''));
            $handle = trim((string) ($field['handle'] ?? '')) ?: Str::slug($label, '_');
            $type = (string) ($field['type'] ?? 'text');

            if ($label === '' || $handle === '' || ! preg_match('/^[A-Za-z0-9_-]+$/', $handle)) {
                throw ValidationException::withMessages([
                    "config.fields.{$index}.label" => 'Setiap subfield Repeater harus memiliki label dan handle yang valid.',
                ]);
            }

            if (! in_array($type, $allowed, true)) {
                throw ValidationException::withMessages([
                    "config.fields.{$index}.type" => 'Tipe subfield Repeater tidak didukung.',
                ]);
            }

            if (collect($fields)->contains(fn (array $item): bool => $item['handle'] === $handle)) {
                throw ValidationException::withMessages([
                    "config.fields.{$index}.handle" => "Handle '{$handle}' dipakai lebih dari sekali.",
                ]);
            }

            $subConfig = [
                'required' => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];

            if (trim((string) ($field['placeholder'] ?? '')) !== '') {
                $subConfig['placeholder'] = trim((string) $field['placeholder']);
            }

            $fields[] = [
                'label' => $label,
                'handle' => $handle,
                'type' => $type,
                'width' => (int) ($field['width'] ?? 100),
                'config' => $subConfig,
            ];
        }

        if ($fields === []) {
            throw ValidationException::withMessages([
                'config.fields' => 'Tambahkan minimal satu subfield yang akan diulang.',
            ]);
        }

        return $fields;
    }
}
