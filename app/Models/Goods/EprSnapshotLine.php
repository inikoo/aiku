<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Models\Goods;

use Illuminate\Database\Eloquent\Model;

/**
 * One SKO and packaging component of a frozen EPR return, with the weights and evidence used when it was frozen.
 *
 * @property int $id
 * @property int $epr_snapshot_id
 * @property string $activity
 * @property int|null $counterparty_country_id
 * @property string|null $channel
 * @property int|null $org_stock_id
 * @property string|null $org_stock_code
 * @property int|null $trade_unit_id
 * @property int|null $packaging_family_id
 * @property string|null $packaging_family_code
 * @property string|null $brand_ownership
 * @property string|null $end_use
 * @property string|null $family_source
 * @property bool|null $is_product_itself
 * @property int|null $packaging_component_id
 * @property string|null $packaging_level
 * @property string|null $material_category
 * @property string|null $polymer
 * @property string|null $weight_g
 * @property bool|null $weight_is_measured
 * @property string|null $quantity_per_unit
 * @property string|null $ram_rating
 * @property \Illuminate\Support\Carbon|null $ram_assessed_at
 * @property string|null $ram_evidence_ref
 * @property string $units
 * @property string $kg
 * @mixin \Eloquent
 */
class EprSnapshotLine extends Model
{
    public $timestamps = false;

    protected $casts = [
        'ram_assessed_at' => 'date',
    ];

    protected $guarded = [];
}
