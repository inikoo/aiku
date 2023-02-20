<?php

namespace App\Models\Procurement;

use App\Models\Marketing\HistoricProductStats;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * App\Models\Procurement\HistoricSupplierProduct
 *
 * @property int $id
 * @property string $slug
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property string|null $deleted_at
 * @property int|null $supplier_product_id
 * @property string $cost unit cost
 * @property string|null $code
 * @property string|null $name
 * @property int|null $units_per_pack
 * @property int|null $units_per_carton
 * @property string|null $cbm
 * @property int|null $currency_id
 * @property int|null $source_id
 * @method static Builder|HistoricSupplierProduct newModelQuery()
 * @method static Builder|HistoricSupplierProduct newQuery()
 * @method static Builder|HistoricSupplierProduct query()
 * @method static Builder|HistoricSupplierProduct whereCbm($value)
 * @method static Builder|HistoricSupplierProduct whereCode($value)
 * @method static Builder|HistoricSupplierProduct whereCost($value)
 * @method static Builder|HistoricSupplierProduct whereCreatedAt($value)
 * @method static Builder|HistoricSupplierProduct whereCurrencyId($value)
 * @method static Builder|HistoricSupplierProduct whereDeletedAt($value)
 * @method static Builder|HistoricSupplierProduct whereId($value)
 * @method static Builder|HistoricSupplierProduct whereName($value)
 * @method static Builder|HistoricSupplierProduct whereSlug($value)
 * @method static Builder|HistoricSupplierProduct whereSourceId($value)
 * @method static Builder|HistoricSupplierProduct whereStatus($value)
 * @method static Builder|HistoricSupplierProduct whereSupplierProductId($value)
 * @method static Builder|HistoricSupplierProduct whereUnitsPerCarton($value)
 * @method static Builder|HistoricSupplierProduct whereUnitsPerPack($value)
 * @mixin \Eloquent
 */
class HistoricSupplierProduct extends Model
{
    use SoftDeletes;
    use HasSlug;

    protected $casts = [
        'status' => 'boolean',
    ];

    public $timestamps = ["created_at"];
    public const UPDATED_AT = null;

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('code')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(64);
    }

    /*
    public function supplierProduct(): BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }
    */

    public function stats(): HasOne
    {
        return $this->hasOne(HistoricProductStats::class);
    }
}
