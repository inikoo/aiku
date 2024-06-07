<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 22:27:25 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Dispatching;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Dispatching\DeliveryNoteStats
 *
 * @property int $id
 * @property int $delivery_note_id
 * @property int $number_items current number of items
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Dispatching\DeliveryNote $deliveryNote
 * @method static Builder|DeliveryNoteStats newModelQuery()
 * @method static Builder|DeliveryNoteStats newQuery()
 * @method static Builder|DeliveryNoteStats query()
 * @mixin Eloquent
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
