<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 18:55:47 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Enums\Inventory\Stock\StockQuantityStatusEnum;
use App\Enums\Inventory\Stock\StockStateEnum;
use App\Enums\Inventory\Stock\StockTradeUnitCompositionEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Barcode;
use App\Models\Media\GroupMedia;
use App\Models\Traits\HasImages;
use App\Models\Traits\HasUniversalSearch;
use Database\Factories\Inventory\StockFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Inventory\Stock
 *
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $owner_type Tenant|Customer
 * @property int $owner_id
 * @property int|null $stock_family_id
 * @property StockTradeUnitCompositionEnum|null $trade_unit_composition
 * @property StockStateEnum $state
 * @property StockQuantityStatusEnum|null $quantity_status
 * @property bool $sellable
 * @property bool $raw_material
 * @property \Illuminate\Database\Eloquent\Collection<int, Barcode> $barcode
 * @property string|null $description
 * @property int|null $units_per_pack units per pack
 * @property int|null $units_per_carton units per carton
 * @property string|null $quantity stock quantity in units
 * @property float|null $available_forecast days
 * @property string|null $value
 * @property int|null $image_id
 * @property array $settings
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $activated_at
 * @property \Illuminate\Support\Carbon|null $discontinuing_at
 * @property \Illuminate\Support\Carbon|null $discontinued_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, GroupMedia> $images
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\Location> $locations
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, GroupMedia> $media
 * @property-read Model|\Eloquent $owner
 * @property-read \App\Models\Inventory\StockStats|null $stats
 * @property-read \App\Models\Inventory\StockFamily|null $stockFamily
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\StockMovement> $stockMovements
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TradeUnit> $tradeUnits
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static StockFactory factory($count = null, $state = [])
 * @method static Builder|Stock newModelQuery()
 * @method static Builder|Stock newQuery()
 * @method static Builder|Stock onlyTrashed()
 * @method static Builder|Stock query()
 * @method static Builder|Stock withTrashed()
 * @method static Builder|Stock withoutTrashed()
 * @mixin \Eloquent
 */
class Stock extends Model implements HasMedia
{
    use SoftDeletes;
    use HasSlug;
    use UsesTenantConnection;
    use HasUniversalSearch;
    use HasImages;
    use HasFactory;

    protected $casts = [
        'data'                   => 'array',
        'settings'               => 'array',
        'activated_at'           => 'datetime',
        'discontinuing_at'       => 'datetime',
        'discontinued_at'        => 'datetime',
        'state'                  => StockStateEnum::class,
        'quantity_status'        => StockQuantityStatusEnum::class,
        'trade_unit_composition' => StockTradeUnitCompositionEnum::class,
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
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }


    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            TradeUnit::class,
            'tenant_'.app('currentTenant')->slug.'.stock_trade_unit',
        )->withPivot(['quantity','notes'])->withTimestamps();
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function images(): BelongsToMany
    {
        return $this->belongsToMany(GroupMedia::class, 'media_stock')->withTimestamps()
            ->withPivot(['public','owner_type','owner_id'])
            ->wherePivot('type', 'image');
    }

    public function barcode(): MorphToMany
    {
        return $this->morphToMany(Barcode::class, 'barcodeable');
    }
}
