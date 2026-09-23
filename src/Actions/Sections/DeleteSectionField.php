<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sections;

use LaraCeemes\Actions\Action;
use LaraCeemes\Models\SectionField;

final class DeleteSectionField extends Action
{
    public function execute(SectionField $field): void
    {
        $this->transaction(function () use ($field): void {
            $field->sectionType->fields()
                ->where('uuid', '!=', $field->uuid)
                ->get()
                ->each(function (SectionField $dependent) use ($field): void {
                    $config = is_array($dependent->config) ? $dependent->config : [];
                    if (data_get($config, 'visibility.field') !== $field->handle) {
                        return;
                    }

                    unset($config['visibility']);
                    $dependent->update(['config' => $config]);
                });
            $field->delete();
        });
    }
}
