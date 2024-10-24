<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 12:40:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\SupplyChain;

use App\Models\Traits\InGroup;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Procurement\HistoricSupplierProduct
 *
 * @property int $id
 * @property int $group_id
 * @property string $slug
 * @property int|null $supplier_product_id
 * @property bool $status
 * @property string|null $code
 * @property string|null $name
 * @property string $cost unit cost
 * @property int|null $units_per_pack
 * @property int|null $units_per_carton
 * @property string|null $cbm
 * @property int|null $currency_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SupplyChain\HistoricSupplierProductStats|null $stats
 * @property-read \App\Models\SupplyChain\SupplierProduct|null $supplierProduct
 * @method static Builder<static>|HistoricSupplierProduct newModelQuery()
 * @method static Builder<static>|HistoricSupplierProduct newQuery()
 * @method static Builder<static>|HistoricSupplierProduct onlyTrashed()
 * @method static Builder<static>|HistoricSupplierProduct query()
 * @method static Builder<static>|HistoricSupplierProduct withTrashed()
 * @method static Builder<static>|HistoricSupplierProduct withoutTrashed()
 * @mixin Eloquent
 */
class HistoricSupplierProduct extends Model
{
    use SoftDeletes;
    use HasSlug;
    use InGroup;

    protected $casts = [
        'status' => 'boolean',
    ];


    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
    }


    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }


    public function stats(): HasOne
    {
        return $this->hasOne(HistoricSupplierProductStats::class);
    }
}
