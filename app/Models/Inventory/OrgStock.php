<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 08:52:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Enums\Inventory\Stock\StockQuantityStatusEnum;
use App\Enums\Inventory\Stock\StockStateEnum;
use App\Enums\Inventory\Stock\StockTradeUnitCompositionEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Barcode;
use App\Models\Media\Media;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Inventory\OrgStock
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $stock_id
 * @property int|null $customer_id
 * @property string $slug
 * @property string $org_state
 * @property bool $org_sellable
 * @property bool $org_raw_material
 * @property \Illuminate\Database\Eloquent\Collection<int, Barcode> $barcode
 * @property string|null $quantity_in_locations stock quantity in units
 * @property StockQuantityStatusEnum|null $quantity_status
 * @property float|null $available_forecast days
 * @property int $number_locations
 * @property string $value_in_locations
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $org_activated_at
 * @property string|null $org_discontinuing_at
 * @property string|null $org_discontinued_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property StockStateEnum $state
 * @property StockTradeUnitCompositionEnum $trade_unit_composition
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $images
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\Location> $locations
 * @property-read Model|\Eloquent $owner
 * @property-read \App\Models\Inventory\StockStats|null $stats
 * @property-read Stock|null $stock
 * @property-read StockFamily|null $stockFamily
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\StockMovement> $stockMovements
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TradeUnit> $tradeUnits
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStock newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStock newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStock onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStock query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStock withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|OrgStock withoutTrashed()
 * @mixin \Eloquent
 */
class OrgStock extends Model
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
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

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(
            TradeUnit::class,
            'stock_trade_unit',
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
        return $this->belongsToMany(Media::class, 'media_stock')->withTimestamps()
            ->withPivot(['public','owner_type','owner_id'])
            ->wherePivot('type', 'image');
    }

    public function barcode(): MorphToMany
    {
        return $this->morphToMany(Barcode::class, 'barcodeable');
    }
}
