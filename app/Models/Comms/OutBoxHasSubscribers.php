<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 05 Mar 2025 11:04:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Models\Comms;

use App\Models\SysAdmin\User;
use App\Models\Traits\InOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property-read \App\Models\SysAdmin\Group|null $group
 * @property-read \App\Models\SysAdmin\Organisation|null $organisation
 * @property-read \App\Models\Comms\Outbox|null $outbox
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutBoxHasSubscribers newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutBoxHasSubscribers newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutBoxHasSubscribers query()
 * @mixin \Eloquent
 */
class OutBoxHasSubscribers extends Model
{
    use InOrganisation;

    protected $table = 'outbox_has_subscribers';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }

}
