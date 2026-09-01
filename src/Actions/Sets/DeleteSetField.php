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
            $field->delete();
            $this->cache->set($handle);
        });
    }
}
