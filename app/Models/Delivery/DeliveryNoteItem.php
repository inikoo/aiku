<?php

namespace App\Models\Delivery;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Delivery\Picking> $pickings
 * @property-read int|null $pickings_count
 * @method static Builder|DeliveryNoteItem onlyTrashed()
 * @method static Builder|DeliveryNoteItem withTrashed()
 * @method static Builder|DeliveryNoteItem withoutTrashed()
 * @mixin \Eloquent
 */
class DeliveryNoteItem extends Model
{
    use SoftDeletes;

    protected $table = 'delivery_note_items';

    protected $casts = [
        'data' => 'array'
    ];

    protected $attributes = [
        'data' => '{}',
    ];

    protected $guarded = [];
    public function pickings(): BelongsToMany
    {
        return $this->belongsToMany(Picking::class)->withTimestamps();
    }

}
