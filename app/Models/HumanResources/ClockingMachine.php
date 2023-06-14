<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Jun 2023 01:53:09 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\HumanResources;

use App\Models\Search\UniversalSearch;
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
 * App\Models\ClockingMachine
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property int $workplace_id
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read UniversalSearch|null $universalSearch
 * @property-read Workplace $workplace
 * @method static Builder|ClockingMachine newModelQuery()
 * @method static Builder|ClockingMachine newQuery()
 * @method static Builder|ClockingMachine onlyTrashed()
 * @method static Builder|ClockingMachine query()
 * @method static Builder|ClockingMachine withTrashed()
 * @method static Builder|ClockingMachine withoutTrashed()
 * @mixin Eloquent
 */
class ClockingMachine extends Model
{
    use UsesTenantConnection;
    use HasSlug;
    use HasUniversalSearch;
    use SoftDeletes;

    protected $casts = [
        'data'        => 'array',
        'status'      => 'boolean',
    ];

    protected $attributes = [
        'data'        => '{}',
    ];

    protected $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(8);
    }

    public function workplace(): BelongsTo
    {
        return $this->belongsTo(Workplace::class);
    }
}
