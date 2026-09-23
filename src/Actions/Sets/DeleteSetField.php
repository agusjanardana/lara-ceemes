<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Sets;

use LaraCeemes\Actions\Action;
use LaraCeemes\Models\SetField;
use LaraCeemes\Support\ContentCacheInvalidator;

final class DeleteSetField extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(SetField $field): void
    {
        $this->transaction(function () use ($field): void {
            $handle = $field->set->handle;
            $field->set->fields()
                ->where('uuid', '!=', $field->uuid)
                ->get()
                ->each(function (SetField $dependent) use ($field): void {
                    $config = is_array($dependent->config) ? $dependent->config : [];
                    if (data_get($config, 'visibility.field') !== $field->handle) {
                        return;
                    }

                    unset($config['visibility']);
                    $dependent->update(['config' => $config]);
                });
            $field->delete();
            $this->cache->set($handle);
        });
    }
}
