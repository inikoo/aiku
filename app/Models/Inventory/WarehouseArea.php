<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:13:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use App\Actions\Utils\Abbreviate;
use App\Models\Search\UniversalSearch;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use Database\Factories\Inventory\WarehouseAreaFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Inventory\WarehouseArea
 *
 * @property int $id
 * @property string $slug
 * @property int $warehouse_id
 * @property string $code
 * @property string $name
 * @property string $unit_quantity
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read array $es_audits
 * @property-read Collection<int, Location> $locations
 * @property-read WarehouseAreaStats|null $stats
 * @property-read UniversalSearch|null $universalSearch
 * @property-read Warehouse $warehouse
 * @method static WarehouseAreaFactory factory($count = null, $state = [])
 * @method static Builder|WarehouseArea newModelQuery()
 * @method static Builder|WarehouseArea newQuery()
 * @method static Builder|WarehouseArea onlyTrashed()
 * @method static Builder|WarehouseArea query()
 * @method static Builder|WarehouseArea withTrashed()
 * @method static Builder|WarehouseArea withoutTrashed()
 * @mixin Eloquent
 */
class WarehouseArea extends Model implements Auditable
{
    use SoftDeletes;
    use HasSlug;
    use UsesTenantConnection;
    use HasUniversalSearch;
    use HasFactory;
    use HasHistory;

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function () {
                return Abbreviate::run($this->code, digits: true, maximumLength: 4);
            })
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(4);
    }



    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WarehouseAreaStats::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
