<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Mar 2023 16:47:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Mail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

/**
 * App\Models\Mail\MailroomStats
 *
 * @property int $id
 * @property int|null $mailroom_id
 * @property int $number_outboxes
 * @property int $number_mailshots
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
 * @property-read \App\Models\Mail\Mailroom|null $mailroom
 * @method static Builder|MailroomStats newModelQuery()
 * @method static Builder|MailroomStats newQuery()
 * @method static Builder|MailroomStats query()
 * @mixin \Eloquent
 */
class MailroomStats extends Model
{
    use UsesTenantConnection;

    protected $table = 'mailroom_stats';

    protected $guarded = [];

    public function mailroom(): BelongsTo
    {
        return $this->belongsTo(Mailroom::class);
    }
}
