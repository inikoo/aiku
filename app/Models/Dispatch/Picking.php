<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Dispatch\Picking
 *
 * @property int $id
 * @property bool $fulfilled
 * @property string $state
 * @property string $status
 * @property int $delivery_note_id
 * @property int|null $stock_movement_id
 * @property int $stock_id
 * @property int|null $picker_id
 * @property int|null $packer_id
 * @property string $required
 * @property string|null $picked
 * @property string|null $weight
 * @property mixed $data
 * @property string|null $assigned_at
 * @property string|null $picking_at
 * @property string|null $picked_at
 * @property string|null $packing_at
 * @property string|null $packed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static Builder|Picking newModelQuery()
 * @method static Builder|Picking newQuery()
 * @method static Builder|Picking query()
 * @method static Builder|Picking whereAssignedAt($value)
 * @method static Builder|Picking whereCreatedAt($value)
 * @method static Builder|Picking whereData($value)
 * @method static Builder|Picking whereDeliveryNoteId($value)
 * @method static Builder|Picking whereFulfilled($value)
 * @method static Builder|Picking whereId($value)
 * @method static Builder|Picking wherePackedAt($value)
 * @method static Builder|Picking wherePackerId($value)
 * @method static Builder|Picking wherePackingAt($value)
 * @method static Builder|Picking wherePicked($value)
 * @method static Builder|Picking wherePickedAt($value)
 * @method static Builder|Picking wherePickerId($value)
 * @method static Builder|Picking wherePickingAt($value)
 * @method static Builder|Picking whereRequired($value)
 * @method static Builder|Picking whereState($value)
 * @method static Builder|Picking whereStatus($value)
 * @method static Builder|Picking whereStockId($value)
 * @method static Builder|Picking whereStockMovementId($value)
 * @method static Builder|Picking whereUpdatedAt($value)
 * @method static Builder|Picking whereWeight($value)
 * @mixin \Eloquent
 */
class Picking extends Model
{
    use HasFactory;
}
