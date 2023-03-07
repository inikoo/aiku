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
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

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
 * @mixin \Eloquent
 */
class DeliveryNoteStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'delivery_note_stats';

    protected $guarded = [];

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
