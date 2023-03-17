<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Mailroom\MailshotStats
 *
 * @property int $id
 * @property int|null $mailshot_id
 * @property int $number_dispatched_emails
 * @property int $number_dispatched_emails_state_ready
 * @property int $number_dispatched_emails_state_sent_to_provider
 * @property int $number_dispatched_emails_state_rejected_by_provider
 * @property int $number_dispatched_emails_state_sent
 * @property int $number_dispatched_emails_state_opened
 * @property int $number_dispatched_emails_state_clicked
 * @property int $number_dispatched_emails_state_soft_bounce
 * @property int $number_dispatched_emails_state_hard_bounce
 * @property int $number_dispatched_emails_state_delivered
 * @property int $number_dispatched_emails_state_marked_as_spam
 * @property int $number_dispatched_emails_state_error
 * @property int $number_provoked_unsubscribe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Mail\Mailshot|null $mailshot
 * @method static Builder|MailshotStats newModelQuery()
 * @method static Builder|MailshotStats newQuery()
 * @method static Builder|MailshotStats query()
 * @mixin \Eloquent
 */
class MailshotStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'mailshot_stats';

    protected $guarded = [];

    public function mailshot(): BelongsTo
    {
        return $this->belongsTo(Mailshot::class);
    }
}
