<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Jun 2023 03:33:04 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\HumanResources;

use App\Enums\HumanResources\TimeTracking\TimeTrackingStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\HumanResources\TimeTracking
 *
 * @property TimeTrackingStatusEnum $status
 * @method static Builder|TimeTracking newModelQuery()
 * @method static Builder|TimeTracking newQuery()
 * @method static Builder|TimeTracking onlyTrashed()
 * @method static Builder|TimeTracking query()
 * @method static Builder|TimeTracking withTrashed()
 * @method static Builder|TimeTracking withoutTrashed()
 * @mixin \Eloquent
 */
class TimeTracking extends Model
{
    use HasSlug;
    use SoftDeletes;

    protected $casts = [
        'status'      => TimeTrackingStatusEnum::class
    ];


    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return $this->starts_at;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }
}
