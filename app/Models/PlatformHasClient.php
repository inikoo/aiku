<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $organisation_id
 * @property int $customer_id
 * @property string $userable_type
 * @property int $userable_id
 * @property int $customer_client_id
 * @property int $platform_customer_client_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformHasClient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformHasClient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PlatformHasClient query()
 * @mixin \Eloquent
 */
class PlatformHasClient extends Pivot
{
    protected $table = 'platform_has_clients';

    protected $guarded = [];
}
