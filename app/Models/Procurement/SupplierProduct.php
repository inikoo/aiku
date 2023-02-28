<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 21:58:23 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSuppliers;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateSupplierProducts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Stancl\Tenancy\Database\Concerns\TenantConnection;

/**
 * App\Models\Procurement\SupplierProduct
 *
 * @property int $id
 * @property string $composition
 * @property string|null $slug
 * @property int|null $current_historic_supplier_product_id
 * @property int|null $supplier_id
 * @property int|null $agent_id
 * @property string|null $state
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
 * @property string|null $global_id
 * @property int|null $source_id
 * @property-read \App\Models\Procurement\Agent|null $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Procurement\HistoricSupplierProduct> $historicRecords
 * @property-read int|null $historic_records_count
 * @property-read \App\Models\Procurement\SupplierProductStats|null $stats
 * @property-read \App\Models\Procurement\Supplier|null $supplier
 * @method static Builder|SupplierProduct newModelQuery()
 * @method static Builder|SupplierProduct newQuery()
 * @method static Builder|SupplierProduct onlyTrashed()
 * @method static Builder|SupplierProduct query()
 * @method static Builder|SupplierProduct whereAgentId($value)
 * @method static Builder|SupplierProduct whereCode($value)
 * @method static Builder|SupplierProduct whereComposition($value)
 * @method static Builder|SupplierProduct whereCost($value)
 * @method static Builder|SupplierProduct whereCreatedAt($value)
 * @method static Builder|SupplierProduct whereCurrentHistoricSupplierProductId($value)
 * @method static Builder|SupplierProduct whereDeletedAt($value)
 * @method static Builder|SupplierProduct whereDescription($value)
 * @method static Builder|SupplierProduct whereGlobalId($value)
 * @method static Builder|SupplierProduct whereId($value)
 * @method static Builder|SupplierProduct whereName($value)
 * @method static Builder|SupplierProduct whereSettings($value)
 * @method static Builder|SupplierProduct whereSharedData($value)
 * @method static Builder|SupplierProduct whereSlug($value)
 * @method static Builder|SupplierProduct whereSourceId($value)
 * @method static Builder|SupplierProduct whereState($value)
 * @method static Builder|SupplierProduct whereStatus($value)
 * @method static Builder|SupplierProduct whereStockQuantityStatus($value)
 * @method static Builder|SupplierProduct whereSupplierId($value)
 * @method static Builder|SupplierProduct whereTenantData($value)
 * @method static Builder|SupplierProduct whereUnitsPerCarton($value)
 * @method static Builder|SupplierProduct whereUnitsPerPack($value)
 * @method static Builder|SupplierProduct whereUpdatedAt($value)
 * @method static Builder|SupplierProduct withTrashed()
 * @method static Builder|SupplierProduct withoutTrashed()
 * @mixin \Eloquent
 */
class SupplierProduct extends Model
{

    use SoftDeletes;
    use HasSlug;
    use TenantConnection;

    protected $casts = [
        'shared_data' => 'array',
        'tenant_data' => 'array',
        'settings'    => 'array',
        'status'      => 'boolean',
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

    protected static function booted()
    {
        static::created(
            function (SupplierProduct $supplierProduct) {
                /** @noinspection PhpUndefinedMethodInspection */
                SupplierHydrateSupplierProducts::dispatch($supplierProduct->supplier()->withTrashed()->first());
                AgentHydrateSuppliers::dispatchIf($supplierProduct->agent_id, $supplierProduct->agent);
            }
        );
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
