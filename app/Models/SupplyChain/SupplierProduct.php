<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 19:33:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use App\Enums\Procurement\SupplierProduct\SupplierProductQuantityStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductTradeUnitCompositionEnum;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\UniversalSearch;
use App\Models\Procurement\HistoricSupplierProduct;
use App\Models\Procurement\OrgSupplierProduct;
use App\Models\Procurement\SupplierProductTradeUnit;
use App\Models\SysAdmin\Group;
use App\Models\Traits\HasHistory;
use App\Models\Traits\HasUniversalSearch;
use App\Models\Traits\InGroup;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\SupplyChain\SupplierProduct
 *
 * @property int $id
 * @property int $group_id
 * @property SupplierProductTradeUnitCompositionEnum|null $trade_unit_composition
 * @property string $slug
 * @property int|null $current_historic_supplier_product_id
 * @property int|null $image_id
 * @property int|null $supplier_id
 * @property int|null $agent_id
 * @property SupplierProductStateEnum|null $state
 * @property bool|null $status
 * @property string|null $stock_quantity_status
 * @property string $code
 * @property string|null $name
 * @property string|null $description
 * @property string $cost unit cost
 * @property int|null $units_per_pack units per pack
 * @property int|null $units_per_carton units per carton
 * @property array $settings
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_slug
 * @property string|null $source_slug_inter_org
 * @property string|null $source_organisation_id
 * @property string|null $source_id
 * @property SupplierProductQuantityStatusEnum $quantity_status
 * @property-read \App\Models\SupplyChain\Agent|null $agent
 * @property-read Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read mixed $gross_weight
 * @property-read Group $group
 * @property-read Collection<int, HistoricSupplierProduct> $historicAssets
 * @property-read mixed $net_weight
 * @property-read Collection<int, OrgSupplierProduct> $orgSupplierProducts
 * @property-read \App\Models\SupplyChain\SupplierProductStats|null $stats
 * @property-read \App\Models\SupplyChain\Supplier|null $supplier
 * @property-read Collection<int, TradeUnit> $tradeUnits
 * @property-read UniversalSearch|null $universalSearch
 * @method static \Database\Factories\SupplyChain\SupplierProductFactory factory($count = null, $state = [])
 * @method static Builder|SupplierProduct newModelQuery()
 * @method static Builder|SupplierProduct newQuery()
 * @method static Builder|SupplierProduct onlyTrashed()
 * @method static Builder|SupplierProduct query()
 * @method static Builder|SupplierProduct withTrashed()
 * @method static Builder|SupplierProduct withoutTrashed()
 * @mixin Eloquent
 */
class SupplierProduct extends Model implements Auditable
{
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;
    use HasFactory;
    use HasHistory;
    use InGroup;

    protected $casts = [
        'cost'                   => 'decimal:4',
        'data'                   => 'array',
        'settings'               => 'array',
        'status'                 => 'boolean',
        'state'                  => SupplierProductStateEnum::class,
        'quantity_status'        => SupplierProductQuantityStatusEnum::class,
        'trade_unit_composition' => SupplierProductTradeUnitCompositionEnum::class,
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function generateTags(): array
    {
        return [
            'supply-chain'
        ];
    }

    protected array $auditInclude = [
        'code',
        'name',
        'status',
        'description',
        'cost',
        'units_per_pack',
        'units_per_carton',
    ];

    public function historicAssets(): HasMany
    {
        return $this->hasMany(HistoricSupplierProduct::class);
    }

    public function stats(): HasOne
    {
        return $this->hasOne(SupplierProductStats::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }


    protected function grossWeight(): Attribute
    {
        return new Attribute(
            get: fn () => $this->tradeUnits()->sum('gross_weight')
        );
    }

    protected function netWeight(): Attribute
    {
        return new Attribute(
            get: fn () => $this->tradeUnits()->sum('net_weight')
        );
    }

    public function tradeUnits(): BelongsToMany
    {
        return $this->belongsToMany(TradeUnit::class)
            ->using(SupplierProductTradeUnit::class)
            ->withPivot('package_quantity')
            ->withTimestamps();
    }

    public function orgSupplierProducts(): HasMany
    {
        return $this->hasMany(OrgSupplierProduct::class);
    }
}
