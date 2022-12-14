<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 21:58:23 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
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
 * @property int|null $sub_supplier_id
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
 * @method static Builder|SupplierProduct newModelQuery()
 * @method static Builder|SupplierProduct newQuery()
 * @method static \Illuminate\Database\Query\Builder|SupplierProduct onlyTrashed()
 * @method static Builder|SupplierProduct query()
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
 * @method static Builder|SupplierProduct whereSubSupplierId($value)
 * @method static Builder|SupplierProduct whereSupplierId($value)
 * @method static Builder|SupplierProduct whereTenantData($value)
 * @method static Builder|SupplierProduct whereUnitsPerCarton($value)
 * @method static Builder|SupplierProduct whereUnitsPerPack($value)
 * @method static Builder|SupplierProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|SupplierProduct withTrashed()
 * @method static \Illuminate\Database\Query\Builder|SupplierProduct withoutTrashed()
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
        'location'    => 'array',
        'status'      => 'boolean',
    ];

    protected $attributes = [
        'shared_data' => '{}',
        'tenant_data' => '{}',
        'settings' => '{}',
        'location' => '{}',

    ];

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug');
    }



}
