<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:06:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * App\Models\Inventory\StockMovement
 *
 * @property int $id
 * @property string $type
 * @property string $stockable_type
 * @property int $stockable_id
 * @property int|null $location_id
 * @property string $quantity
 * @property string $amount
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $source_id
 * @property-read Model|\Eloquent $stockable
 * @method static Builder|StockMovement newModelQuery()
 * @method static Builder|StockMovement newQuery()
 * @method static Builder|StockMovement query()
 * @method static Builder|StockMovement whereAmount($value)
 * @method static Builder|StockMovement whereCreatedAt($value)
 * @method static Builder|StockMovement whereData($value)
 * @method static Builder|StockMovement whereId($value)
 * @method static Builder|StockMovement whereLocationId($value)
 * @method static Builder|StockMovement whereQuantity($value)
 * @method static Builder|StockMovement whereSourceId($value)
 * @method static Builder|StockMovement whereStockableId($value)
 * @method static Builder|StockMovement whereStockableType($value)
 * @method static Builder|StockMovement whereType($value)
 * @method static Builder|StockMovement whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StockMovement extends Model
{
    protected $casts = [
        'data' => 'array'
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
    public function setQuantityAttribute($val)
    {
        $this->attributes['quantity'] = sprintf('%.3f', $val);
    }

    /** @noinspection PhpUnused */
    public function setAmountAttribute($val)
    {
        $this->attributes['amount'] = sprintf('%.3f', $val);
    }
}
