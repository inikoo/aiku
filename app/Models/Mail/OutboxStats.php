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
 * App\Models\Mail\OutboxStats
 *
 * @property int $id
 * @property int|null $outbox_id
 * @property int $number_mailshots
 * @property int $number_emails
 * @property int $number_error_emails
 * @property int $number_rejected_emails
 * @property int $number_sent_emails
 * @property int $number_delivered_emails
 * @property int $number_hard_bounced_emails
 * @property int $number_soft_bounced_emails
 * @property int $number_opened_emails
 * @property int $number_clicked_emails
 * @property int $number_spam_emails
 * @property int $number_unsubscribed_emails
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
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\Mail\Outbox|null $outbox
 * @method static Builder|OutboxStats newModelQuery()
 * @method static Builder|OutboxStats newQuery()
 * @method static Builder|OutboxStats query()
 * @method static Builder|OutboxStats whereCreatedAt($value)
 * @method static Builder|OutboxStats whereId($value)
 * @method static Builder|OutboxStats whereNumberClickedEmails($value)
 * @method static Builder|OutboxStats whereNumberDeliveredEmails($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmails($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateClicked($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateDelivered($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateError($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateHardBounce($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateOpened($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateReady($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateRejected($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateSent($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateSoftBounce($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateSpam($value)
 * @method static Builder|OutboxStats whereNumberDispatchedEmailsStateUnsubscribed($value)
 * @method static Builder|OutboxStats whereNumberEmails($value)
 * @method static Builder|OutboxStats whereNumberErrorEmails($value)
 * @method static Builder|OutboxStats whereNumberHardBouncedEmails($value)
 * @method static Builder|OutboxStats whereNumberMailshots($value)
 * @method static Builder|OutboxStats whereNumberOpenedEmails($value)
 * @method static Builder|OutboxStats whereNumberRejectedEmails($value)
 * @method static Builder|OutboxStats whereNumberSentEmails($value)
 * @method static Builder|OutboxStats whereNumberSoftBouncedEmails($value)
 * @method static Builder|OutboxStats whereNumberSpamEmails($value)
 * @method static Builder|OutboxStats whereNumberUnsubscribedEmails($value)
 * @method static Builder|OutboxStats whereOutboxId($value)
 * @method static Builder|OutboxStats whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OutboxStats extends Model
{
    protected $table = 'outbox_stats';

    protected $guarded = [];

    public function outbox(): BelongsTo
    {
        return $this->belongsTo(Outbox::class);
    }
}
