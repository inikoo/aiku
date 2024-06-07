<?php

namespace App\Models\Dispatching;

use App\Models\SysAdmin\Organisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Dispatching\ShippingEvent
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property string $provider_type
 * @property int $provider_id
 * @property string $sent_at
 * @property string|null $received_at
 * @property string $events
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Organisation $organisation
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingEvent query()
 * @mixin \Eloquent
 */
class ShippingEvent extends Model
{
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
