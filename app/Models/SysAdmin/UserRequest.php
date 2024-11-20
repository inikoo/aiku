<?php
/*
 * author Arya Permana - Kirin
 * created on 20-11-2024-11h-21m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Models\SysAdmin;

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
