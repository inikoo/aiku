<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 21:48:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Marketing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Marketing\HistoricProduct
 *
 * @property int $id
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property string|null $deleted_at
 * @property int|null $product_id
 * @property string $price unit price
 * @property string|null $code
 * @property string|null $name
 * @property string|null $pack units per pack
 * @property string|null $outer units per outer
 * @property string|null $carton units per carton
 * @property string|null $cbm to be deleted
 * @property int|null $currency_id
 * @method static Builder|HistoricProduct newModelQuery()
 * @method static Builder|HistoricProduct newQuery()
 * @method static Builder|HistoricProduct query()
 * @method static Builder|HistoricProduct whereCarton($value)
 * @method static Builder|HistoricProduct whereCbm($value)
 * @method static Builder|HistoricProduct whereCode($value)
 * @method static Builder|HistoricProduct whereCreatedAt($value)
 * @method static Builder|HistoricProduct whereCurrencyId($value)
 * @method static Builder|HistoricProduct whereDeletedAt($value)
 * @method static Builder|HistoricProduct whereId($value)
 * @method static Builder|HistoricProduct whereName($value)
 * @method static Builder|HistoricProduct whereOuter($value)
 * @method static Builder|HistoricProduct wherePack($value)
 * @method static Builder|HistoricProduct wherePrice($value)
 * @method static Builder|HistoricProduct whereProductId($value)
 * @method static Builder|HistoricProduct whereStatus($value)
 * @mixin \Eloquent
 * @property int $organisation_id
 * @property int|null $organisation_source_id
 * @property-read \App\Models\Marketing\Product|null $product
 * @method static \Illuminate\Database\Query\Builder|HistoricProduct onlyTrashed()
 * @method static Builder|HistoricProduct whereOrganisationId($value)
 * @method static Builder|HistoricProduct whereOrganisationSourceId($value)
 * @method static \Illuminate\Database\Query\Builder|HistoricProduct withTrashed()
 * @method static \Illuminate\Database\Query\Builder|HistoricProduct withoutTrashed()
 */
class HistoricProduct extends Model
{
    use SoftDeletes;

    protected $casts = [
        'status' => 'boolean',

    ];

    public $timestamps = ["created_at"];
    public const UPDATED_AT = null;

    protected $guarded = [];

    public function setPriceAttribute($val)
    {
        $this->attributes['price'] = sprintf('%.2f', $val);
    }

    public function setCbmAttribute($val)
    {
        $this->attributes['cbm'] = sprintf('%.4f', $val);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
