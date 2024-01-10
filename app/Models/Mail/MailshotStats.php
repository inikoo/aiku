<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\Mail\MailshotStats
 *
 * @property int $id
 * @property int|null $mailshot_id
 * @property int $number_dispatched_emails
 * @property int $number_dispatched_emails_state_ready
 * @property int $number_dispatched_emails_state_error
 * @property int $number_dispatched_emails_state_rejected
 * @property int $number_dispatched_emails_state_sent
 * @property int $number_dispatched_emails_state_delivered
 * @property int $number_dispatched_emails_state_hard_bounce
 * @property int $number_dispatched_emails_state_soft_bounce
 * @property int $number_dispatched_emails_state_opened
 * @property int $number_dispatched_emails_state_clicked
 * @property int $number_dispatched_emails_state_spam
 * @property int $number_dispatched_emails_state_unsubscribed
 * @property int $number_provoked_unsubscribe
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Mail\Mailshot|null $mailshot
 * @method static Builder|MailshotStats newModelQuery()
 * @method static Builder|MailshotStats newQuery()
 * @method static Builder|MailshotStats query()
 * @mixin Eloquent
 */
class MailshotStats extends Model
{
    protected $table = 'mailshot_stats';

    protected $guarded = [];

    public function mailshot(): BelongsTo
    {
        return $this->belongsTo(Mailshot::class);
    }
}
