<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 19:55:29 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Catalogue;

use App\Models\Traits\HasHistory;
use App\Models\Traits\InShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int|null $shop_id
 * @property int|null $asset_id
 * @property int|null $family_id
 * @property int|null $department_id
 * @property int|null $product_variant_id
 * @property string $code
 * @property string|null $name
 * @property string|null $price
 * @property string $ratio
 * @property string $unit
 * @property string $units
 * @property int $currency_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Helpers\Audit> $audits
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\Organisation $organisation
 * @property-read \App\Models\Catalogue\ProductVariant|null $productVariant
 * @property-read \App\Models\Catalogue\HistoricProductVariantSalesInterval|null $salesIntervals
 * @property-read \App\Models\Catalogue\Shop|null $shop
 * @property-read \App\Models\Catalogue\HistoricProductVariantStats|null $stats
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|HistoricProductVariant query()
 * @mixin \Eloquent
 */
class HistoricProductVariant extends Model implements Auditable
{
    use HasHistory;
    use InShop;

    protected $guarded = [];

    public function stats(): HasOne
    {
        return $this->hasOne(HistoricProductVariantStats::class);
    }

    public function salesIntervals(): HasOne
    {
        return $this->hasOne(HistoricProductVariantSalesInterval::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

}
