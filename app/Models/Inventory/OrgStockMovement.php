<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:06:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Enums\Inventory\OrgStockMovement\OrgStockMovementFlowEnum;
use App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $warehouse_id
 * @property int|null $warehouse_area_id
 * @property Carbon $date
 * @property string $class
 * @property OrgStockMovementTypeEnum $type
 * @property OrgStockMovementFlowEnum $flow
 * @property bool $is_delivered
 * @property bool $is_received
 * @property int $org_stock_id
 * @property int|null $location_id
 * @property string|null $operation_type
 * @property int|null $operation_id
 * @property string $quantity
 * @property string $amount
 * @property string $group_amount
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property string|null $source_id
 * @property-read \App\Models\Inventory\Location|null $location
 * @property-read \App\Models\Inventory\OrgStock $orgStock
 * @method static Builder|OrgStockMovement newModelQuery()
 * @method static Builder|OrgStockMovement newQuery()
 * @method static Builder|OrgStockMovement query()
 * @mixin Eloquent
 */
class OrgStockMovement extends Model
{
    protected $casts = [
        'data'         => 'array',
        'type'         => OrgStockMovementTypeEnum::class,
        'flow'         => OrgStockMovementFlowEnum::class,
        'date'         => 'datetime',
        'quantity'     => 'decimal:3',
        'amount'       => 'decimal:3',
        'group_amount' => 'decimal:3',
        'org_amount'   => 'decimal:3',
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function orgStock(): BelongsTo
    {
        return $this->belongsTo(OrgStock::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
