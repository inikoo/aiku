<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:50:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Dispatch\DeliveryNoteItem
 *
 * @property int $id
 * @property int $delivery_note_id
 * @property int|null $stock_id
 * @property int|null $transaction_id
 * @property int|null $picking_id
 * @property string $state
 * @property string $status
 * @property string $required
 * @property string $quantity
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property int|null $source_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Dispatch\Picking> $pickings
 * @property-read int|null $pickings_count
 * @method static Builder|DeliveryNoteItem newModelQuery()
 * @method static Builder|DeliveryNoteItem newQuery()
 * @method static Builder|DeliveryNoteItem onlyTrashed()
 * @method static Builder|DeliveryNoteItem query()
 * @method static Builder|DeliveryNoteItem whereCreatedAt($value)
 * @method static Builder|DeliveryNoteItem whereData($value)
 * @method static Builder|DeliveryNoteItem whereDeletedAt($value)
 * @method static Builder|DeliveryNoteItem whereDeliveryNoteId($value)
 * @method static Builder|DeliveryNoteItem whereId($value)
 * @method static Builder|DeliveryNoteItem wherePickingId($value)
 * @method static Builder|DeliveryNoteItem whereQuantity($value)
 * @method static Builder|DeliveryNoteItem whereRequired($value)
 * @method static Builder|DeliveryNoteItem whereSourceId($value)
 * @method static Builder|DeliveryNoteItem whereState($value)
 * @method static Builder|DeliveryNoteItem whereStatus($value)
 * @method static Builder|DeliveryNoteItem whereStockId($value)
 * @method static Builder|DeliveryNoteItem whereTransactionId($value)
 * @method static Builder|DeliveryNoteItem whereUpdatedAt($value)
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
