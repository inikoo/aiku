<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:30:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CRM\WebUserStats
 *
 * @property int $id
 * @property int $web_user_id
 * @property int $number_logins
 * @property string|null $last_login_at
 * @property string|null $last_login_ip
 * @property string|null $last_active_at
 * @property int $number_failed_logins
 * @property string|null $last_failed_login_ip
 * @property string|null $last_failed_login_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CRM\WebUser $webUser
 * @method static \Illuminate\Database\Eloquent\Builder|WebUserStats newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebUserStats newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WebUserStats query()
 * @mixin \Eloquent
 */
class WebUserStats extends Model
{
    protected $table = 'web_user_stats';

    protected $guarded = [];

    public function webUser(): BelongsTo
    {
        return $this->belongsTo(WebUser::class);
    }
}
