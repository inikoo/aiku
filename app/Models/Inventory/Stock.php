<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 18:55:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Actions\Central\Tenant\HydrateTenant;
use App\Actions\Inventory\StockFamily\HydrateStockFamily;
use App\Models\Marketing\TradeUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\TenantConnection;

/**
 * App\Models\Inventory\Stock
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $owner_type
 * @property int $owner_id
 * @property int|null $stock_family_id
 * @property string $composition
 * @property string|null $state
 * @property string|null $quantity_status
 * @property bool $sellable
 * @property bool $raw_material
 * @property string|null $barcode
 * @property string|null $description
 * @property int|null $units_per_pack units per pack
 * @property int|null $units_per_carton units per carton
 * @property string|null $quantity stock quantity in units
 * @property float|null $available_forecast days
 * @property string|null $value
 * @property int|null $image_id
 * @property int|null $package_image_id
 * @property array $settings
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $activated_at
 * @property \Illuminate\Support\Carbon|null $discontinuing_at
 * @property \Illuminate\Support\Carbon|null $discontinued_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\Location> $locations
 * @property-read int|null $locations_count
 * @property-read Model|\Eloquent $owner
 * @property-read \App\Models\Inventory\StockStats|null $stats
 * @property-read \App\Models\Inventory\StockFamily|null $stockFamily
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\StockMovement> $stockMovements
 * @property-read int|null $stock_movements_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TradeUnit> $tradeUnits
 * @property-read int|null $trade_units_count
 * @method static Builder|Stock newModelQuery()
 * @method static Builder|Stock newQuery()
 * @method static Builder|Stock onlyTrashed()
 * @method static Builder|Stock query()
 * @method static Builder|Stock whereActivatedAt($value)
 * @method static Builder|Stock whereAvailableForecast($value)
 * @method static Builder|Stock whereBarcode($value)
 * @method static Builder|Stock whereCode($value)
 * @method static Builder|Stock whereComposition($value)
 * @method static Builder|Stock whereCreatedAt($value)
 * @method static Builder|Stock whereData($value)
 * @method static Builder|Stock whereDeletedAt($value)
 * @method static Builder|Stock whereDescription($value)
 * @method static Builder|Stock whereDiscontinuedAt($value)
 * @method static Builder|Stock whereDiscontinuingAt($value)
 * @method static Builder|Stock whereId($value)
 * @method static Builder|Stock whereImageId($value)
 * @method static Builder|Stock whereOwnerId($value)
 * @method static Builder|Stock whereOwnerType($value)
 * @method static Builder|Stock wherePackageImageId($value)
 * @method static Builder|Stock whereQuantity($value)
 * @method static Builder|Stock whereQuantityStatus($value)
 * @method static Builder|Stock whereRawMaterial($value)
 * @method static Builder|Stock whereSellable($value)
 * @method static Builder|Stock whereSettings($value)
 * @method static Builder|Stock whereSlug($value)
 * @method static Builder|Stock whereSourceId($value)
 * @method static Builder|Stock whereState($value)
 * @method static Builder|Stock whereStockFamilyId($value)
 * @method static Builder|Stock whereUnitsPerCarton($value)
 * @method static Builder|Stock whereUnitsPerPack($value)
 * @method static Builder|Stock whereUpdatedAt($value)
 * @method static Builder|Stock whereValue($value)
 * @method static Builder|Stock withTrashed()
 * @method static Builder|Stock withoutTrashed()
 * @mixin \Eloquent
 */
class Stock extends Model
{
    use SoftDeletes;
    use TenantConnection;
    use HasSlug;

    protected $casts = [
        'data' => 'array',
        'settings' => 'array',
        'activated_at' => 'datetime',
        'discontinuing_at' => 'datetime',
        'discontinued_at' => 'datetime',
    ];

    protected $attributes = [
        'data' => '{}',
        'settings' => '{}',
    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
    }


    protected static function booted()
    {
        static::created(
            function (Stock $stock) {
                HydrateTenant::make()->inventoryStats();
                if($stock->stock_family_id){
                    HydrateStockFamily::make()->productsStats($stock->stockFamily);
                }
            }
        );
        static::deleted(
            function (Stock $stock) {
                HydrateTenant::make()->inventoryStats();
                if($stock->stock_family_id){
                    HydrateStockFamily::make()->productsStats($stock->stockFamily);
                }
            }
        );

    }


    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(TradeUnit::class)->withPivot('quantity')->withTimestamps();
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class)->using(LocationStock::class)->withTimestamps()
            ->withPivot('quantity');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'stockable');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(StockStats::class);
    }

    public function stockFamily(): BelongsTo
    {
        return $this->belongsTo(StockFamily::class);
    }


}
