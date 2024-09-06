<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Sept 2024 12:40:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Procurement;

use App\Models\SupplyChain\SupplierProduct;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Procurement\HistoricSupplierProduct
 *
 * @property int $id
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string|null $source_id
 * @property-read \App\Models\Procurement\HistoricSupplierProductStats|null $stats
 * @property-read SupplierProduct|null $supplierProduct
 * @method static Builder|HistoricSupplierProduct newModelQuery()
 * @method static Builder|HistoricSupplierProduct newQuery()
 * @method static Builder|HistoricSupplierProduct onlyTrashed()
 * @method static Builder|HistoricSupplierProduct query()
 * @method static Builder|HistoricSupplierProduct withTrashed()
 * @method static Builder|HistoricSupplierProduct withoutTrashed()
 * @mixin Eloquent
 */
class HistoricSupplierProduct extends Model
{
    use SoftDeletes;
    use HasSlug;

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
