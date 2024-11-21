<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Nov 2024 10:22:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Analytics;

use App\Models\SysAdmin\User;
use App\Models\Traits\InGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $date
 * @property string $route_name
 * @property string $route_params
 * @property string $section
 * @property string $os
 * @property string $device
 * @property string $browser
 * @property string $ip_address
 * @property string $location
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\Group $group
 * @property-read \App\Models\SysAdmin\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserRequest query()
 * @mixin \Eloquent
 */
class UserRequest extends Model
{
    use InGroup;

    protected $guarded = [
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
