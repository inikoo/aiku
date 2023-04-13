<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 21:58:23 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Enums\Procurement\SupplierProduct\SupplierProductQuantityStatusEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductStateEnum;
use App\Enums\Procurement\SupplierProduct\SupplierProductTradeUnitCompositionEnum;
use App\Models\Traits\HasUniversalSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Procurement\SupplierProduct
 *
 * @property int $id
 * @property SupplierProductTradeUnitCompositionEnum|null $trade_unit_composition
 * @property string|null $slug
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
 * @property array $shared_data
 * @property array $tenant_data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $central_supplier_product_id
 * @property int|null $source_id
 * @property SupplierProductQuantityStatusEnum $quantity_status
 * @property-read \App\Models\Procurement\Agent|null $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Procurement\HistoricSupplierProduct> $historicRecords
 * @property-read \App\Models\Procurement\SupplierProductStats|null $stats
 * @property-read \App\Models\Procurement\Supplier|null $supplier
 * @property-read \App\Models\Search\UniversalSearch|null $universalSearch
 * @method static Builder|SupplierProduct newModelQuery()
 * @method static Builder|SupplierProduct newQuery()
 * @method static Builder|SupplierProduct onlyTrashed()
 * @method static Builder|SupplierProduct query()
 * @method static Builder|SupplierProduct withTrashed()
 * @method static Builder|SupplierProduct withoutTrashed()
 * @mixin \Eloquent
 */
class SupplierProduct extends Model
{
    use UsesTenantConnection;
    use SoftDeletes;
    use HasSlug;
    use HasUniversalSearch;

    protected $casts = [
        'shared_data'            => 'array',
        'tenant_data'            => 'array',
        'settings'               => 'array',
        'status'                 => 'boolean',
        'state'                  => SupplierProductStateEnum::class,
        'quantity_status'        => SupplierProductQuantityStatusEnum::class,
        'trade_unit_composition' => SupplierProductTradeUnitCompositionEnum::class,
    ];

    protected $attributes = [
        'shared_data' => '{}',
        'tenant_data' => '{}',
        'settings'    => '{}',

    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
    }

    public function historicRecords(): HasMany
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
