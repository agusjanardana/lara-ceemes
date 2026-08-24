<?php

declare(strict_types=1);

namespace LaraCeemes\Actions\Blueprints;

use LaraCeemes\Actions\Action;
use LaraCeemes\Models\BlueprintField;
use LaraCeemes\Support\ContentCacheInvalidator;

final class DeleteBlueprintField extends Action
{
    public function __construct(private readonly ContentCacheInvalidator $cache) {}

    public function execute(BlueprintField $field): void
    {
        $this->transaction(function () use ($field): void {
            $collectionHandle = $field->blueprint->collection->handle;
            $field->delete();
            $this->cache->collection($collectionHandle);
        });
    }
}
