<?php

declare(strict_types=1);

namespace LaraCeemes\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use LaraCeemes\Actions\Sets\CreateSetField;
use LaraCeemes\Actions\Sets\DeleteSetField;
use LaraCeemes\Actions\Sets\UpdateSetField;
use LaraCeemes\Fields\FieldRegistry;
use LaraCeemes\Models\Content;
use LaraCeemes\Models\Set;
use LaraCeemes\Models\SetField;

final class FieldController extends AdminController
{
    public function __construct(private readonly FieldRegistry $registry) {}

    public function store(Request $request, Set $set, CreateSetField $action): RedirectResponse
    {
        $data = $request->all();
        $data['config'] = $this->fieldConfig($request);
        $action->execute($set, $data);

        return $this->redirectToContent($request, $set, 'Fixed Field ditambahkan.');
    }

    public function update(Request $request, SetField $field, UpdateSetField $action): RedirectResponse
    {
        $data = $request->all();
        $data['config'] = $this->fieldConfig($request);
        $action->execute($field, $data);

        return $this->redirectToContent($request, $field->set, 'Fixed Field diperbarui.');
    }

    public function destroy(Request $request, SetField $field, DeleteSetField $action): RedirectResponse
    {
        $set = $field->set;
        $action->execute($field);

        return $this->redirectToContent($request, $set, 'Fixed Field dihapus.');
    }

    private function redirectToContent(Request $request, Set $set, string $message): RedirectResponse
    {
        $contentUuid = $request->string('redirect_content_uuid')->toString();
        $content = Content::query()
            ->where('set_uuid', $set->uuid)
            ->when($contentUuid !== '', fn ($query) => $query->whereKey($contentUuid))
            ->first();

        if ($content !== null) {
            return $this->success('ceemes.admin.contents.edit', $message, $content);
        }

        return $this->success('ceemes.admin.contents.index', $message, $set);
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

        if (in_array($request->string('type')->toString(), ['content', 'content'], true)) {
            $set = is_string($config['set'] ?? null) ? $config['set'] : '';

            if (! Set::query()->where('handle', $set)->exists()) {
                throw ValidationException::withMessages([
                    'config.set' => 'Pilih Set sumber untuk Content field.',
                ]);
            }
        } else {
            unset($config['set']);
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
