<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 08:52:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Enums\Inventory\OrgStock\OrgStockQuantityStatusEnum;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Models\SupplyChain\Stock;
use App\Models\SysAdmin\Organisation;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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
 * @property int|null $org_stock_family_id
 * @property int|null $customer_id
 * @property string $slug
 * @property string $code
 * @property string|null $name
 * @property string|null $unit_value
 * @property string $unit_commercial_value
 * @property bool $is_sellable_in_organisation
 * @property bool $is_raw_material_in_organisation
 * @property OrgStockStateEnum $state
 * @property OrgStockQuantityStatusEnum|null $quantity_status
 * @property string|null $quantity_in_locations stock quantity in units
 * @property string $value_in_locations
 * @property float|null $available_forecast days
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $activated_in_organisation_at
 * @property string|null $discontinuing_in_organisation_at
 * @property string|null $discontinued_in_organisation_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \App\Models\Inventory\LocationOrgStock $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\Location> $locations
 * @property-read \App\Models\Inventory\OrgStockFamily|null $orgStockFamily
 * @property-read Organisation $organisation
 * @property-read Model|\Eloquent $owner
 * @property-read \App\Models\Inventory\OrgStockStats|null $stats
 * @property-read Stock|null $stock
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory\StockMovement> $stockMovements
 * @property-read \App\Models\Helpers\UniversalSearch|null $universalSearch
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
        'data'             => 'array',
        'activated_at'     => 'datetime',
        'discontinuing_at' => 'datetime',
        'discontinued_at'  => 'datetime',
        'state'            => OrgStockStateEnum::class,
        'quantity_status'  => OrgStockQuantityStatusEnum::class,
    ];

    protected $attributes = [
        'data' => '{}',
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
                return $this->code.' '.$this->organisation->code;
            })
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }


    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }


    public function orgStockFamily(): BelongsTo
    {
        return $this->belongsTo(OrgStockFamily::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class)->using(LocationOrgStock::class)->withTimestamps()
            ->withPivot(
                ['id', 'group_id', 'organisation_id', 'warehouse_id', 'warehouse_area_id', 'quantity', 'value', 'commercial_value', 'type', 'picking_priority', 'notes', 'data', 'settings', 'audited_at', 'source_stock_id', 'source_location_id', 'dropshipping_pipe']
            );
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
        return $this->hasOne(OrgStockStats::class);
    }


}
