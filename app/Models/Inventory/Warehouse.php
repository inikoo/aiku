<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:11:40 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Models\Inventory;

use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Inventory\Warehouse
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $name
 * @property array $settings
 * @property array $data
 * @property int|null $image_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\Location> $locations
 * @property-read \App\Models\Inventory\WarehouseStats|null $stats
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\WarehouseArea> $warehouseAreas
 * @method static Builder|Warehouse newModelQuery()
 * @method static Builder|Warehouse newQuery()
 * @method static Builder|Warehouse onlyTrashed()
 * @method static Builder|Warehouse query()
 * @method static Builder|Warehouse withTrashed()
 * @method static Builder|Warehouse withoutTrashed()
 * @mixin \Eloquent
 */
class Warehouse extends Model
{
    use HasSlug;
    use SoftDeletes;
    use UsesTenantConnection;
    use HasUniversalSearch;

    protected $casts = [
        'data'     => 'array',
        'settings' => 'array'
    ];

    protected $attributes = [
        'data'     => '{}',
        'settings' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(4);
    }


    public function warehouseAreas(): HasMany
    {
        return $this->hasMany(WarehouseArea::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(WarehouseStats::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
