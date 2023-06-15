<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:15:46 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Auth;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Auth\UserStats
 *
 * @property int $id
 * @property int $user_id
 * @property int $number_logins
 * @property int $number_other_tenants
 * @property int $number_other_active_tenants
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Auth\User $user
 * @method static Builder|UserStats newModelQuery()
 * @method static Builder|UserStats newQuery()
 * @method static Builder|UserStats query()
 * @mixin Eloquent
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
