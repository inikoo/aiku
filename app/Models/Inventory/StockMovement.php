<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:06:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use App\Enums\Inventory\StockMovement\StockMovementFlowEnum;
use App\Enums\Inventory\StockMovement\StockMovementTypeEnum;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Inventory\StockMovement
 *
 * @property int $id
 * @property StockMovementTypeEnum $type
 * @property StockMovementFlowEnum $flow
 * @property string $stockable_type
 * @property int $stockable_id
 * @property int|null $location_id
 * @property string|null $operation_type
 * @property int|null $operation_id
 * @property string $quantity
 * @property string $amount
 * @property array $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $source_id
 * @property-read Model|\Eloquent $stockable
 * @method static Builder|StockMovement newModelQuery()
 * @method static Builder|StockMovement newQuery()
 * @method static Builder|StockMovement query()
 * @mixin Eloquent
 */
class StockMovement extends Model
{
    protected $casts = [
        'data' => 'array',
        'type' => StockMovementTypeEnum::class,
        'flow' => StockMovementFlowEnum::class
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];

    public function stockable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @noinspection PhpUnused */
    public function setQuantityAttribute($val): void
    {
        $this->attributes['quantity'] = sprintf('%.3f', $val);
    }

    /** @noinspection PhpUnused */
    public function setAmountAttribute($val): void
    {
        $this->attributes['amount'] = sprintf('%.3f', $val);
    }
}
