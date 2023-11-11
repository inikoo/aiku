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
use Illuminate\Support\Carbon;

/**
 * App\Models\Mail\EmailAddress
 *
 * @property int $id
 * @property string $email
 * @property string|null $soft_bounced_at
 * @property string|null $hard_bounced_at
 * @property int $number_dispatches
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Mail\Mailshot $mailshot
 * @method static Builder|EmailAddress newModelQuery()
 * @method static Builder|EmailAddress newQuery()
 * @method static Builder|EmailAddress query()
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
