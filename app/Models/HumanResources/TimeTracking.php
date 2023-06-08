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
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\HumanResources\TimeTracking
 *
 * @property int $id
 * @property string $slug
 * @property TimeTrackingStatusEnum $status
 * @property string $subject_type Employee|Guest
 * @property int $subject_id
 * @property int|null $workplace_id
 * @property string|null $starts_at
 * @property string|null $ends_at
 * @property int|null $start_clocking_id
 * @property int|null $end_clocking_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
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
    use UsesTenantConnection;
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
