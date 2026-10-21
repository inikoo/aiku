<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:20
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

namespace App\Models\GoodsIn;

use App\Enums\SupplyChain\PackingSpec\PackingCheckResultEnum;
use App\Models\SupplyChain\SupplierProductPackingSpec;
use App\Models\SysAdmin\User;
use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Goods in checking a delivered supplier product against its packing spec: one carton in twenty weighed gross, and on
 * the first delivery of a new spec version each packing material weighed.
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $stock_delivery_id
 * @property int $stock_delivery_item_id
 * @property int $packing_spec_id
 * @property bool $weigh_materials
 * @property int $cartons_to_check
 * @property array<int, float> $carton_weights_g
 * @property string|null $deviation_pct
 * @property PackingCheckResultEnum $result
 * @property int|null $staff_task_id
 * @property int|null $checked_by_user_id
 * @property Carbon|null $checked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read StockDelivery $stockDelivery
 * @property-read StockDeliveryItem $stockDeliveryItem
 * @property-read SupplierProductPackingSpec $packingSpec
 * @property-read User|null $checkedBy
 * @mixin \Eloquent
 */
class StockDeliveryPackingCheck extends Model
{
    use InOrganisation;

    public const int CARTONS_PER_CHECK = 20;

    protected $casts = [
        'weigh_materials'  => 'boolean',
        'carton_weights_g' => 'array',
        'result'           => PackingCheckResultEnum::class,
        'checked_at'       => 'datetime',
    ];

    protected $attributes = [
        'carton_weights_g' => '[]',
    ];

    protected $guarded = [];

    public function stockDelivery(): BelongsTo
    {
        return $this->belongsTo(StockDelivery::class);
    }

    public function stockDeliveryItem(): BelongsTo
    {
        return $this->belongsTo(StockDeliveryItem::class);
    }

    public function packingSpec(): BelongsTo
    {
        return $this->belongsTo(SupplierProductPackingSpec::class, 'packing_spec_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_user_id');
    }
}
