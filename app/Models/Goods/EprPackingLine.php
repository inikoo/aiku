<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:20
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Models\Goods;

use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One packing material that arrived inside the cartons of a received stock delivery item, in kg, rebuilt from the
 * supplier product's packing spec by BuildEprPackingLines. Reported once, at import, whatever the warehouse did with it.
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property Carbon $date
 * @property string $activity
 * @property int|null $stock_delivery_id
 * @property int|null $stock_delivery_item_id
 * @property int|null $agent_id
 * @property int|null $supplier_product_id
 * @property int|null $packing_spec_id
 * @property int|null $packing_spec_line_id
 * @property int|null $packaging_component_id
 * @property int|null $counterparty_country_id
 * @property string|null $level
 * @property string $material_category
 * @property string|null $polymer
 * @property string|null $fate
 * @property string|null $spec_status
 * @property bool $weight_is_measured
 * @property bool $is_household
 * @property bool $in_product_packaging passed on to the buyer and already in the weighed packaging of the product
 * @property string|null $shop_type
 * @property string $cartons
 * @property string $quantity
 * @property string $kg
 * @property Carbon|null $created_at
 * @mixin \Eloquent
 */
class EprPackingLine extends Model
{
    use InOrganisation;

    public const UPDATED_AT = null;

    protected $casts = [
        'date'                 => 'date',
        'weight_is_measured'   => 'boolean',
        'is_household'         => 'boolean',
        'in_product_packaging' => 'boolean',
    ];

    protected $guarded = [];
}
