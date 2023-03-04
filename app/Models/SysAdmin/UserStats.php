<?php

namespace App\Models\SysAdmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\SysAdmin\UserStats
 *
 * @property int $id
 * @property int $user_id
 * @property int $number_other_tenants
 * @property int $number_other_active_tenants
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserStats query()
 * @mixin \Eloquent
 */
class UserStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'user_stats';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
