<?php

namespace App\Models\Delivery;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Delivery\DeliveryNoteItem
 *
 * @property int $id
 * @property int|null $delivery_note_id
 * @property int|null $picking_id
 * @property string $order_item_type
 * @property int $order_item_id
 * @property int|null $stock_id
 * @property string|null $quantity
 * @property mixed $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static Builder|DeliveryNoteItem newModelQuery()
 * @method static Builder|DeliveryNoteItem newQuery()
 * @method static Builder|DeliveryNoteItem query()
 * @method static Builder|DeliveryNoteItem whereCreatedAt($value)
 * @method static Builder|DeliveryNoteItem whereData($value)
 * @method static Builder|DeliveryNoteItem whereDeletedAt($value)
 * @method static Builder|DeliveryNoteItem whereDeliveryNoteId($value)
 * @method static Builder|DeliveryNoteItem whereId($value)
 * @method static Builder|DeliveryNoteItem whereOrderItemId($value)
 * @method static Builder|DeliveryNoteItem whereOrderItemType($value)
 * @method static Builder|DeliveryNoteItem wherePickingId($value)
 * @method static Builder|DeliveryNoteItem whereQuantity($value)
 * @method static Builder|DeliveryNoteItem whereStockId($value)
 * @method static Builder|DeliveryNoteItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DeliveryNoteItem extends Model
{
    use HasFactory;
}
