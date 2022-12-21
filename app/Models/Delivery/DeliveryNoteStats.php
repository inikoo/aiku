<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Dec 2022 14:59:26 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Models\Delivery;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Delivery\DeliveryNoteStats
 *
 * @property int $id
 * @property int $delivery_note_id
 * @property int $number_items current number of items
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Delivery\DeliveryNote $deliveryNote
 * @method static Builder|DeliveryNoteStats newModelQuery()
 * @method static Builder|DeliveryNoteStats newQuery()
 * @method static Builder|DeliveryNoteStats query()
 * @method static Builder|DeliveryNoteStats whereCreatedAt($value)
 * @method static Builder|DeliveryNoteStats whereDeliveryNoteId($value)
 * @method static Builder|DeliveryNoteStats whereId($value)
 * @method static Builder|DeliveryNoteStats whereNumberItems($value)
 * @method static Builder|DeliveryNoteStats whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DeliveryNoteStats extends Model
{
    protected $table = 'delivery_note_stats';
    protected $guarded = [];

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
