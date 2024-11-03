<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 08 Mar 2023 23:33:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Mail\EmailAddress
 *
 * @property int $id
 * @property string $email
 * @property string|null $last_marketing_dispatch_at
 * @property string|null $last_transactional_dispatch_at
 * @property string|null $soft_bounced_at
 * @property string|null $hard_bounced_at
 * @property int $number_marketing_dispatches
 * @property int $number_transactional_dispatches
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Mail\Mailshot|null $mailshot
 * @method static Builder<static>|EmailAddress newModelQuery()
 * @method static Builder<static>|EmailAddress newQuery()
 * @method static Builder<static>|EmailAddress query()
 * @mixin Eloquent
 */
class EmailAddress extends Model
{
    protected $guarded = [];

    public function mailshot(): BelongsTo
    {
        return $this->belongsTo(Mailshot::class);
    }
}
