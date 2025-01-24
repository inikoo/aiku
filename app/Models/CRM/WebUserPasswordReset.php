<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Jan 2025 20:03:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property-read \App\Models\CRM\WebUser|null $webUser
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebUserPasswordReset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebUserPasswordReset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebUserPasswordReset query()
 * @mixin \Eloquent
 */
class WebUserPasswordReset extends Model
{
    protected $guarded = [];

    public function webUser(): BelongsTo
    {
        return $this->belongsTo(WebUser::class);
    }

}
