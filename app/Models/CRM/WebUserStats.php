<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:30:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebUserStats extends Model
{
    protected $table = 'web_user_stats';

    protected $guarded = [];

    public function webUser(): BelongsTo
    {
        return $this->belongsTo(WebUser::class);
    }
}
