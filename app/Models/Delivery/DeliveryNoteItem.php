<?php

namespace App\Models\Delivery;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Delivery\DeliveryNoteItem
 *
 * @property int $id
 * @property int|null $transaction_id
 * @property int $delivery_note_id
 * @property int|null $order_id
 * @property int|null $historic_product_id
 * @property string|null $quantity
 * @property mixed $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem whereDeliveryNoteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem whereHistoricProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DeliveryNoteItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DeliveryNoteItem extends Model
{
    use HasFactory;
}
