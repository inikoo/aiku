<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 10:03:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Actions\Central\Tenant\Hydrators\TenantHydrateInventory;
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
 * App\Models\Inventory\StockFamily
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string|null $state
 * @property string|null $name
 * @property string|null $description
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \App\Models\Inventory\StockFamilyStats|null $stats
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\Stock> $stocks
 * @method static Builder|StockFamily newModelQuery()
 * @method static Builder|StockFamily newQuery()
 * @method static Builder|StockFamily onlyTrashed()
 * @method static Builder|StockFamily query()
 * @method static Builder|StockFamily withTrashed()
 * @method static Builder|StockFamily withoutTrashed()
 * @mixin \Eloquent
 */
class StockFamily extends Model
{
    use HasSlug;
    use SoftDeletes;
    use UsesTenantConnection;
    use HasUniversalSearch;

    protected $casts = [
        'data'       => 'array',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    protected static function booted()
    {
        static::updated(function (StockFamily $stockFamily) {
            if ($stockFamily->wasChanged('state')) {
                TenantHydrateInventory::dispatch(app('currentTenant'));
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->slugsShouldBeNoLongerThan(32)
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(StockFamilyStats::class);
    }
}
