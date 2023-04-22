<?php

namespace App\Models\Procurement;

use App\Models\Marketing\HistoricProductStats;
use App\Models\Traits\UsesGroupConnection;
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
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $supplier_product_id
 * @property string $cost unit cost
 * @property string|null $code
 * @property string|null $name
 * @property int|null $units_per_pack
 * @property int|null $units_per_carton
 * @property string|null $cbm
 * @property int|null $currency_id
 * @property int|null $central_historic_supplier_product_id
 * @property int|null $source_id
 * @property-read HistoricProductStats|null $stats
 * @method static Builder|HistoricSupplierProduct newModelQuery()
 * @method static Builder|HistoricSupplierProduct newQuery()
 * @method static Builder|HistoricSupplierProduct onlyTrashed()
 * @method static Builder|HistoricSupplierProduct query()
 * @method static Builder|HistoricSupplierProduct withTrashed()
 * @method static Builder|HistoricSupplierProduct withoutTrashed()
 * @mixin \Eloquent
 */
class HistoricSupplierProduct extends Model
{
    use UsesGroupConnection;
    use SoftDeletes;
    use HasSlug;

    protected $casts = [
        'status' => 'boolean',
    ];

    public $timestamps      = ["created_at"];
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
