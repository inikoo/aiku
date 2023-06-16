<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Jun 2023 03:43:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\HumanResources;

use App\Enums\HumanResources\Clocking\ClockingTypeEnum;
use App\Models\Traits\HasUniversalSearch;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\HumanResources\Clocking
 *
 * @property int $id
 * @property string $slug
 * @property ClockingTypeEnum $type
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property int|null $time_tracking_id
 * @property int|null $workplace_id
 * @property int|null $clocking_machine_id
 * @property string $clocked_at
 * @property string|null $generator_type
 * @property int|null $generator_id
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $deleted_by_type
 * @property int|null $deleted_by_id
 * @property int|null $source_id
 * @property-read Workplace $workplace
 * @property-read ClockingMachine|null $clockingMachine
 * @method static Builder|Clocking newModelQuery()
 * @method static Builder|Clocking newQuery()
 * @method static Builder|Clocking onlyTrashed()
 * @method static Builder|Clocking query()
 * @method static Builder|Clocking withTrashed()
 * @method static Builder|Clocking withoutTrashed()
 * @mixin Eloquent
 */
class Clocking extends Model
{
    use UsesTenantConnection;
    use HasSlug;
    use HasUniversalSearch;
    use SoftDeletes;

    protected $casts = [
        'type'      => ClockingTypeEnum::class
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
                return $this->clocked_at;
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }

    public function clockingMachine(): BelongsTo
    {
        return $this->belongsTo(ClockingMachine::class);
    }

}
