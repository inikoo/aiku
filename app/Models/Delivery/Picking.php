<?php

namespace App\Models\Delivery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Delivery\Picking
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
 * @method static \Illuminate\Database\Eloquent\Builder|Picking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Picking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Picking query()
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereAssignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereDeliveryNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereFulfilled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking wherePackedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking wherePackerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking wherePackingAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking wherePicked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking wherePickedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking wherePickerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking wherePickingAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereStockId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereStockMovementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Picking whereWeight($value)
 * @mixin \Eloquent
 */
class Picking extends Model
{
    use HasFactory;
}
