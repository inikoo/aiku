<?php

namespace App\Models\Dispatch;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Dispatch\ShippingEvent
 *
 * @property int $id
 * @property string $provider_type
 * @property int $provider_id
 * @property string $sent_at
 * @property string|null $received_at
 * @property string $events
 * @property string $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShippingEvent query()
 * @mixin \Eloquent
 */
class ShippingEvent extends Model
{
}
