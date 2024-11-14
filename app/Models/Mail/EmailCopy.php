<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 13 Nov 2024 21:15:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 *
 * @property int $id
 * @property int $dispatched_email_id
 * @property string $subject
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $fetched_at
 * @property string|null $last_fetched_at
 * @property string|null $source_id
 * @property-read \App\Models\Mail\DispatchedEmail $dispatchedEmail
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCopy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCopy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCopy query()
 * @mixin \Eloquent
 */
class EmailCopy extends Model
{
    protected $casts = [
        'fetched_at'         => 'datetime',
        'last_fetched_at'    => 'datetime',
    ];

    protected $guarded = [];

    public function dispatchedEmail(): BelongsTo
    {
        return $this->belongsTo(DispatchedEmail::class);
    }

}