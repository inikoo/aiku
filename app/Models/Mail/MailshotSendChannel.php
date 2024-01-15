<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Nov 2023 15:51:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use App\Enums\Mail\MailshotSendChannel\MailshotSendChannelStateEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Mail\MailshotSendChannel
 *
 * @property int $id
 * @property int|null $mailshot_id
 * @property int $number_emails
 * @property MailshotSendChannelStateEnum $state
 * @property string|null $start_sending_at
 * @property string|null $sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Mail\Mailshot|null $mailshot
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel query()
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel whereMailshotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel whereNumberEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel whereStartSendingAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MailshotSendChannel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MailshotSendChannel extends Model
{
    protected $casts = [

        'state' => MailshotSendChannelStateEnum::class

    ];

    protected $guarded = [];

    public function mailshot(): BelongsTo
    {
        return $this->belongsTo(Mailshot::class);
    }

}
