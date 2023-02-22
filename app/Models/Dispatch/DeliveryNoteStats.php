<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Dispatch\DeliveryNoteStats
 *
 * @property int $id
 * @property int $delivery_note_id
 * @property int $number_items current number of items
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Dispatch\DeliveryNote $deliveryNote
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
