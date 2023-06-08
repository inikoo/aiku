<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 10:03:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateInventory;
use App\Enums\Inventory\StockFamily\StockFamilyStateEnum;
use App\Models\Search\UniversalSearch;
use App\Models\Traits\HasUniversalSearch;
use Database\Factories\Inventory\StockFamilyFactory;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Inventory\StockFamily
 *
 * @property int $id
 * @property string $slug
 * @property StockFamilyStateEnum $state
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read StockFamilyStats|null $stats
 * @property-read Collection<int, Stock> $stocks
 * @property-read UniversalSearch|null $universalSearch
 * @method static StockFamilyFactory factory($count = null, $state = [])
 * @method static Builder|StockFamily newModelQuery()
 * @method static Builder|StockFamily newQuery()
 * @method static Builder|StockFamily onlyTrashed()
 * @method static Builder|StockFamily query()
 * @method static Builder|StockFamily withTrashed()
 * @method static Builder|StockFamily withoutTrashed()
 * @mixin Eloquent
 */
class StockFamily extends Model
{
    use HasSlug;
    use SoftDeletes;
    use UsesTenantConnection;
    use HasUniversalSearch;
    use HasFactory;

    protected $casts = [
        'data'  => 'array',
        'state' => StockFamilyStateEnum::class,

    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    protected static function booted(): void
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

    public function getRouteKeyName(): string
    {
        return 'slug';
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
