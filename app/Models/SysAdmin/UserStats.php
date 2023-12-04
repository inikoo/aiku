<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:30:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\SysAdmin;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\SysAdmin\UserStats
 *
 * @property int $id
 * @property int $user_id
 * @property int $number_logins
 * @property string|null $last_login_at
 * @property string|null $last_login_ip
 * @property string|null $last_active_at
 * @property int $number_failed_logins
 * @property string|null $last_failed_login_ip
 * @property string|null $last_failed_login_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SysAdmin\User $user
 * @method static Builder|UserStats newModelQuery()
 * @method static Builder|UserStats newQuery()
 * @method static Builder|UserStats query()
 * @mixin Eloquent
 */
class UserStats extends Model
{
    protected $table = 'user_stats';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
