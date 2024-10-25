<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 25 Oct 2024 16:48:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 *
 *
 * @property int $id
 * @property int $mailshot_id
 * @property int $dispatched_email_id
 * @property string $recipient_type
 * @property int $recipient_id
 * @property int $channel
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Mail\DispatchedEmail $dispatchedEmail
 * @property-read \App\Models\Mail\Mailshot $mailshot
 * @property-read Model|\Eloquent $recipient
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailshotRecipient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailshotRecipient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailshotRecipient query()
 * @mixin \Eloquent
 */
class MailshotRecipient extends Model
{
    protected $guarded = [];

    public function recipient(): MorphTo
    {
        return $this->morphTo();
    }

    public function mailshot(): BelongsTo
    {
        return $this->belongsTo(Mailshot::class);
    }

    public function dispatchedEmail(): BelongsTo
    {
        return $this->belongsTo(DispatchedEmail::class);
    }
}
