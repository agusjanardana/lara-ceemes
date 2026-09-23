<?php

declare(strict_types=1);

namespace LaraCeemes\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LaraCeemes\Models\Site;
use LaraCeemes\Support\SiteContext;

trait BelongsToSite
{
    protected static function bootBelongsToSite(): void
    {
        static::addGlobalScope('ceemes_site', function (Builder $builder): void {
            $siteUuid = app(SiteContext::class)->uuid();
            if ($siteUuid !== null) {
                $builder->where($builder->qualifyColumn('site_uuid'), $siteUuid);
            }
        });

        static::creating(function ($model): void {
            if ($model->getAttribute('site_uuid') === null) {
                $model->setAttribute('site_uuid', app(SiteContext::class)->current()->uuid);
            }
        });
    }

    /** @return BelongsTo<Site, $this> */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_uuid', 'uuid');
    }
}
